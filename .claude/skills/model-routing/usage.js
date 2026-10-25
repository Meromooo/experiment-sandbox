#!/usr/bin/env node
// Token use per model and per session, read from Claude Code's local session logs for this
// repo (every worktree included). Shows whether the helpers are taking the long reads and the
// repeated edits, and how big the main conversation gets per call.
//
//   node .claude/skills/model-routing/usage.js        last 7 days
//   node .claude/skills/model-routing/usage.js 30     last 30 days
//   node .claude/skills/model-routing/usage.js all    everything
//
// Reads only; prints token counts, never message content.
const fs = require('fs');
const os = require('os');
const path = require('path');
const { execFileSync } = require('child_process');

const arg = process.argv[2] || '7';
const since = arg === 'all' ? '' : new Date(Date.now() - Number(arg) * 864e5).toISOString();

// Claude Code names a project's log folder after its path, every other character turned to '-'.
const repo = path.dirname(execFileSync('git', ['rev-parse', '--path-format=absolute', '--git-common-dir']).toString().trim());
const prefix = path.resolve(repo).replace(/[^a-zA-Z0-9]/g, '-');
const root = path.join(process.env.CLAUDE_CONFIG_DIR || path.join(os.homedir(), '.claude'), 'projects');
const files = [];
const walk = (dir) => {
	for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
		const p = path.join(dir, e.name);
		if (e.isDirectory()) walk(p);
		else if (e.name.endsWith('.jsonl')) files.push(p);
	}
};
fs.readdirSync(root).filter((d) => d.startsWith(prefix)).forEach((d) => walk(path.join(root, d)));

// One record per API call: streamed replies repeat a message id on several lines, last one wins.
const calls = new Map();
for (const file of files) {
	const helper = file.includes(`${path.sep}subagents${path.sep}`);
	for (const line of fs.readFileSync(file, 'utf8').split('\n')) {
		if (!line.includes('"usage"')) continue;
		let d;
		try { d = JSON.parse(line); } catch { continue; }
		const m = d.message;
		if (!m || !m.usage || !m.model || m.model.startsWith('<') || d.timestamp < since) continue;
		const u = m.usage;
		calls.set(m.id || d.uuid, {
			session: d.sessionId, day: d.timestamp.slice(0, 10), model: m.model,
			helper: helper || d.isSidechain === true,
			context: (u.input_tokens || 0) + (u.cache_creation_input_tokens || 0) + (u.cache_read_input_tokens || 0),
			output: u.output_tokens || 0,
		});
	}
}
if (!calls.size) { console.log(`No calls found since ${since || 'the start'} under ${root}`); process.exit(0); }

const k = (n) => (n >= 1e9 ? `${(n / 1e9).toFixed(2)}B` : n >= 1e6 ? `${(n / 1e6).toFixed(1)}M` : `${Math.round(n / 1e3)}k`);
const byModel = {};
const bySession = {};
for (const c of calls.values()) {
	const m = (byModel[`${c.model}|${c.helper ? 'helper' : 'main'}`] ??= { calls: 0, context: 0, output: 0 });
	m.calls++; m.context += c.context; m.output += c.output;
	const s = (bySession[c.session] ??= { day: c.day, main: 0, ctx: 0, max: 0, helpers: {} });
	s.day = c.day > s.day ? c.day : s.day;
	if (c.helper) s.helpers[c.model] = (s.helpers[c.model] || 0) + 1;
	else { s.main++; s.ctx += c.context; s.max = Math.max(s.max, c.context); }
}

console.log(`Since ${since.slice(0, 10) || 'the start'}: ${calls.size} calls, ${Object.keys(bySession).length} sessions\n`);
console.log('model'.padEnd(28) + 'role'.padEnd(8) + 'calls'.padStart(7) + 'output'.padStart(9) + 'context read'.padStart(14));
for (const [key, m] of Object.entries(byModel).sort((a, b) => b[1].context - a[1].context)) {
	const [model, role] = key.split('|');
	console.log(model.padEnd(28) + role.padEnd(8) + String(m.calls).padStart(7) + k(m.output).padStart(9) + k(m.context).padStart(14));
}
const all = [...calls.values()];
const main = all.filter((c) => !c.helper);
console.log(`\nHelper share of calls: ${Math.round((100 * (all.length - main.length)) / all.length)}%`);
if (main.length) console.log(`Main conversation per call: ${k(main.reduce((t, c) => t + c.context, 0) / main.length)} on average`);

console.log('\nday         session   main calls  avg ctx  max ctx  helper calls');
for (const [id, s] of Object.entries(bySession).sort((a, b) => (a[1].day < b[1].day ? -1 : 1))) {
	const helpers = Object.entries(s.helpers).map(([m, n]) => `${n} ${m.replace('claude-', '')}`).join(', ') || '-';
	const avg = s.main ? k(s.ctx / s.main) : '-';
	console.log(`${s.day}  ${id.slice(0, 8)}  ${String(s.main).padStart(10)}  ${avg.padStart(7)}  ${k(s.max).padStart(7)}  ${helpers}`);
}
