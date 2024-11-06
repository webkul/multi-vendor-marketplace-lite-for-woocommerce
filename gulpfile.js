/**
 * Gulp js file for minimying css file.
 */
const gulp     = require( 'gulp' ); // npm install --save-dev gulp gulp-clean-css gulp-rename
const rename   = require( 'gulp-rename' );
const watch    = require( 'gulp-watch' );
const cleanCSS = require( 'gulp-clean-css' );

gulp.task(
	'MinfyAdminCSS',
	function () {
		return gulp.src( './src/admin/css/admin.css' )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: ".min" } ) )
		.pipe( gulp.dest( './assets/admin/css/' ) );
	}
);
gulp.task(
	'MinfyInvoiceCSS',
	function () {
		return gulp.src( './src/admin/css/invoice-style.css' )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: ".min" } ) )
		.pipe( gulp.dest( './assets/admin/css/' ) );
	}
);

gulp.task(
	'MinfyFrontCSS',
	function () {
		return gulp.src( './src/front/css/front.css' )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: ".min" } ) )
		.pipe( gulp.dest( './assets/front/css/' ) )
	}
);
gulp.task(
	'MinfyStyleCSS',
	function () {
		return gulp.src( './src/front/css/style.css' )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: ".min" } ) )
		.pipe( gulp.dest( './assets/front/css/' ) )
	}
);
gulp.task(
	'MinfyThemeCSS',
	function () {
		return gulp.src( './src/front/css/wkmp-theme-compatibility.css' )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: ".min" } ) )
		.pipe( gulp.dest( './assets/front/css/' ) )
	}
);
gulp.task(
	'MinfyAccountCSS',
	function () {
		return gulp.src( './src/front/css/myaccount-style.css' )
		.pipe( cleanCSS() )
		.pipe( rename( { suffix: ".min" } ) )
		.pipe( gulp.dest( './assets/front/css/' ) )
	}
);
gulp.task(
	'watch',
	function () {
		return gulp.watch( ['./src/admin/css/admin.css','./src/front/css/front.css','./src/front/css/style.css'], gulp.series( 'default' ) );
	}
);

gulp.task( 'default', gulp.series( ['MinfyAdminCSS', 'MinfyInvoiceCSS', 'MinfyFrontCSS', 'MinfyStyleCSS', 'MinfyThemeCSS', 'MinfyAccountCSS'] ) );
gulp.task( 'watch', gulp.series( ['watch'] ) );
