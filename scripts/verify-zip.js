#!/usr/bin/env node

/**
 * Verify the contents of the built plugin ZIP.
 */

const fs = require('fs');
const path = require('path');

const zipPath = path.join(__dirname, '..', 'dist', 'ki-kraft.zip');

if (!fs.existsSync(zipPath)) {
	console.error('✗ ZIP file not found:', zipPath);
	process.exit(1);
}

const stats = fs.statSync(zipPath);
console.log('✓ ZIP file exists:', zipPath);
console.log('✓ Size:', stats.size, 'bytes');

// TODO: Add additional verification logic
// - Check for required files
// - Verify no development files are included
// - Check file structure

console.log('✓ Verification complete');
