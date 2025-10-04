#!/usr/bin/env node

/**
 * Synchronize version numbers across plugin files.
 */

const fs = require('fs');
const path = require('path');

const version = process.argv[2];

if (!version) {
	console.error('Usage: node sync-version.js <version>');
	process.exit(1);
}

console.log(`Syncing version to ${version}...`);

// Update plugin header
const pluginFile = path.join(__dirname, '..', 'ki-kraft.php');
let pluginContent = fs.readFileSync(pluginFile, 'utf8');
pluginContent = pluginContent.replace(
	/Version: [\d.]+/,
	`Version: ${version}`
);
pluginContent = pluginContent.replace(
	/define\( 'KRAFT_AI_CHAT_VERSION', '[\d.]+' \);/,
	`define( 'KRAFT_AI_CHAT_VERSION', '${version}' );`
);
pluginContent = pluginContent.replace(
	/define\( 'KI_KRAFT_VERSION', KRAFT_AI_CHAT_VERSION \);/,
	`define( 'KI_KRAFT_VERSION', KRAFT_AI_CHAT_VERSION );`
);
fs.writeFileSync(pluginFile, pluginContent);
console.log('✓ Updated ki-kraft.php');

// Update package.json if it exists
const packageFile = path.join(__dirname, '..', 'package.json');
if (fs.existsSync(packageFile)) {
	const packageJson = JSON.parse(fs.readFileSync(packageFile, 'utf8'));
	packageJson.version = version;
	fs.writeFileSync(packageFile, JSON.stringify(packageJson, null, 2) + '\n');
	console.log('✓ Updated package.json');
}

console.log('✓ Version sync complete');
