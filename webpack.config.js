const path = require('path');

module.exports = [
	// Admin build
	{
		entry: './admin/index.tsx',
		output: {
			path: path.resolve(__dirname, 'assets'),
			filename: 'admin.js',
		},
		resolve: {
			extensions: ['.ts', '.tsx', '.js', '.jsx'],
		},
		module: {
			rules: [
				{
					test: /\.(ts|tsx)$/,
					use: 'ts-loader',
					exclude: /node_modules/,
				},
				{
					test: /\.css$/,
					use: ['style-loader', 'css-loader'],
				},
			],
		},
		externals: {
			react: 'React',
			'react-dom': 'ReactDOM',
		},
	},
	// Frontend build
	{
		entry: './frontend/index.ts',
		output: {
			path: path.resolve(__dirname, 'assets'),
			filename: 'widget.js',
		},
		resolve: {
			extensions: ['.ts', '.js'],
		},
		module: {
			rules: [
				{
					test: /\.ts$/,
					use: 'ts-loader',
					exclude: /node_modules/,
				},
				{
					test: /\.css$/,
					use: ['style-loader', 'css-loader'],
				},
			],
		},
	},
];
