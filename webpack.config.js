const path    = require( 'path' );
const nodeEnv = process.env.nodeEnv || 'development';

module.exports = {
	mode: nodeEnv,
	devtool: nodeEnv !== 'production' ? 'source-map' : false,
	watch: true,
	entry: {
		adminJS: './src/admin/js/admin.js',
		frontJS: './src/front/js/front.js',
		frontBlockJS: './src/front/js/front-block.js',
	},
	output: {
		filename: (data) => {
			return data.chunk.name === 'adminJS' ? 'assets/admin/js/admin.min.js' : data.chunk.name === 'frontJS' ? 'assets/front/js/front.min.js' : data.chunk.name === 'frontBlockJS' ? 'assets/front/js/front-block.min.js' : [];
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
