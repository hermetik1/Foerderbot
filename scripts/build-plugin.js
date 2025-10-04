#!/usr/bin/env node

/**
 * Build script for KI Kraft plugin.
 * Creates a production-ready ZIP file.
 */

const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const distDir = path.join(__dirname, '..', 'dist');
const zipPath = path.join(distDir, 'ki-kraft.zip');

// Create dist directory if it doesn't exist
if (!fs.existsSync(distDir)) {
	fs.mkdirSync(distDir, { recursive: true });
}

// Create output stream
const output = fs.createWriteStream(zipPath);
const archive = archiver('zip', { zlib: { level: 9 } });

output.on('close', () => {
	console.log(`✓ Plugin packaged: ${archive.pointer()} bytes`);
	console.log(`✓ Output: ${zipPath}`);
});

archive.on('error', (err) => {
	throw err;
});

archive.pipe(output);

// Add files to archive, excluding development files
const excludePatterns = [
	'.git',
	'.github',
	'tests',
	'node_modules',
	'vendor',
	'dist',
	'.gitignore',
	'.eslintrc',
	'.prettierrc',
	'composer.json',
	'composer.lock',
	'package.json',
	'package-lock.json',
	'phpcs.xml',
	'phpstan.neon'
];

console.log('Building plugin package...');

// Add plugin files
archive.directory('../', 'ki-kraft', (entry) => {
	const relativePath = entry.name.replace(/\\/g, '/');
	
	// Exclude development files and directories
	for (const pattern of excludePatterns) {
		if (relativePath.startsWith(pattern + '/') || relativePath === pattern) {
			return false;
		}
	}
	
	return entry;
});

archive.finalize();
