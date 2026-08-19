const fs = require("fs");
const path = require("path");

function walk(directory) {
  return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
    const target = path.join(directory, entry.name);
    if (entry.isDirectory()) return walk(target);
    return entry.name === "index.html" ? [target] : [];
  });
}

const files = walk(path.join(__dirname, ".."));
const issues = [];
let structuredDataBlocks = 0;
const publicRoot = path.join(__dirname, "..");
const missingLinks = new Map();

for (const file of files) {
  const html = fs.readFileSync(file, "utf8");
  const blocks = [...html.matchAll(/<script[^>]+type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi)];
  blocks.forEach((block, index) => {
    structuredDataBlocks++;
    try {
      JSON.parse(block[1]);
    } catch (error) {
      issues.push({ file, structuredDataBlock: index + 1, error: error.message });
    }
  });
  const canonical = (html.match(/<link[^>]+rel=["']canonical["']/gi) || []).length;
  const title = (html.match(/<title>/gi) || []).length;
  const h1 = (html.match(/<h1(?:\s|>)/gi) || []).length;
  const isNoindex = /<meta[^>]+name=["']robots["'][^>]+content=["'][^"']*noindex/i.test(html);
  if ((!isNoindex && canonical !== 1) || title !== 1 || h1 !== 1) {
    issues.push({ file, canonical, title, h1 });
  }

  for (const match of html.matchAll(/href=["'](\/[^"']*)["']/gi)) {
    const pathname = decodeURIComponent(match[1].split(/[?#]/, 1)[0]);
    const clean = pathname.replace(/^\/+/, "");
    const target = path.join(publicRoot, clean);
    const exists = clean === "" || fs.existsSync(target) || fs.existsSync(path.join(target, "index.html"));
    if (!exists) {
      if (!missingLinks.has(pathname)) missingLinks.set(pathname, []);
      if (missingLinks.get(pathname).length < 3) missingLinks.get(pathname).push(path.relative(publicRoot, file));
    }
  }
}

for (const [href, sources] of missingLinks) issues.push({ missingInternalLink: href, sources });

console.log(JSON.stringify({ pages: files.length, structuredDataBlocks, issueCount: issues.length, issues }, null, 2));
process.exitCode = issues.length ? 1 : 0;
