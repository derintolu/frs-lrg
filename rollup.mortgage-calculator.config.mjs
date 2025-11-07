import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import terser from '@rollup/plugin-terser';
import replace from '@rollup/plugin-replace';
import {babel} from '@rollup/plugin-babel';

const isProduction = process.env.NODE_ENV === 'production';

export default {
	input: './src/widget/mortgage-calculator/index.tsx',
	output: {
		file: './assets/dist/mortgage-calculator-widget.js',
		format: 'iife',
		name: 'MortgageCalculatorWidget',
		sourcemap: !isProduction,
		inlineDynamicImports: true,
		globals: {
			react: 'React',
			'react-dom': 'ReactDOM',
			'react/jsx-runtime': 'jsxRuntime',
		},
	},
	external: ['react', 'react-dom', 'react/jsx-runtime'],
	plugins: [
		replace({
			'process.env.NODE_ENV': JSON.stringify(
				isProduction ? 'production' : 'development',
			),
			preventAssignment: true,
		}),
		resolve({
			extensions: ['.js', '.jsx', '.ts', '.tsx'],
			dedupe: ['react', 'react-dom'],
		}),
		commonjs(),
		babel({
			babelHelpers: 'bundled',
			exclude: 'node_modules/**',
			extensions: ['.js', '.jsx', '.ts', '.tsx'],
			presets: [
				'@babel/preset-react',
				[
					'@babel/preset-typescript',
					{
						isTSX: true,
						allExtensions: true,
					},
				],
			],
		}),
		isProduction &&
			terser({
				ecma: 2020,
				mangle: {toplevel: true},
				compress: {
					module: true,
					toplevel: true,
					unsafe_arrows: true,
					drop_console: true,
					drop_debugger: true,
				},
				output: {quote_style: 1},
			}),
	].filter(Boolean),
};
