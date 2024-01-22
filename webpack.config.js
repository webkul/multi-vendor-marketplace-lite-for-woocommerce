const path    = require( 'path' );
const nodeEnv = process.env.nodeEnv || 'development';

module.exports = {
	mode: nodeEnv,
	devtool: nodeEnv !== 'production' ? 'source-map' : false,
	watch: true,
	entry: {
		adminJS: './assets/build/admin/js/admin.js',
		frontJS: './assets/build/front/js/front.js',
	},
	output: {
		filename: (data) => {
			return data.chunk.name === 'adminJS' ? 'assets/dist/admin/js/admin.min.js' : data.chunk.name === 'frontJS' ? 'assets/dist/front/js/front.min.js' : [];
		},
		path: path.resolve( __dirname ),
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: "babel-loader",
					options: {
						presets: ['@babel/preset-env']
					}
				}
		},

		]
	},
}
