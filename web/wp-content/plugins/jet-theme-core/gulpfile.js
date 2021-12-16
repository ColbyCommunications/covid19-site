'use strict';

let gulp            = require( 'gulp' ),
	rename          = require( 'gulp-rename' ),
	notify          = require( 'gulp-notify' ),
	uglify          = require( 'gulp-uglify-es' ).default,
	sass            = require( 'gulp-sass' ),
	plumber         = require( 'gulp-plumber' ),
	autoprefixer    = require( 'gulp-autoprefixer' ),
	checktextdomain = require( 'gulp-checktextdomain' );

gulp.task('admin-css', () => {
	return gulp.src('./assets/scss/admin.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( { outputStyle: 'compressed' } ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))

		.pipe(rename('admin.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('preview-css', () => {
	return gulp.src('./assets/scss/preview.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( { outputStyle: 'compressed' } ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))

		.pipe(rename('preview.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('editor-css', () => {
	return gulp.src('./assets/scss/editor.scss')
		.pipe(
			plumber( {
				errorHandler: function ( error ) {
					console.log('=================ERROR=================');
					console.log(error.message);
					this.emit( 'end' );
				}
			})
		)
		.pipe(sass( { outputStyle: 'compressed' } ))
		.pipe(autoprefixer({
				browsers: ['last 10 versions'],
				cascade: false
		}))

		.pipe(rename('editor.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task( 'js-editor-minify', function() {
	return gulp.src( './assets/js/editor.js' )
		.pipe( uglify() )
		.pipe( rename( { extname: '.min.js' } ) )
		.pipe( gulp.dest( './assets/js/' ) )
		.pipe( notify( 'Editor.js Minify Done!' ) );
} );

//watch
gulp.task( 'watch', function() {
	gulp.watch( './assets/js/editor.js', gulp.series( 'js-editor-minify' ) );
	gulp.watch( './assets/scss/**', gulp.series( ...[ 'admin-css', 'preview-css', 'editor-css' ] ) );
} );

gulp.task( 'checktextdomain', function() {
	return gulp.src( ['**/*.php', '!framework/**/*.php'] )
		.pipe( checktextdomain( {
			text_domain: 'jet-theme-core',
			keywords:    [
				'__:1,2d',
				'_e:1,2d',
				'_x:1,2c,3d',
				'esc_html__:1,2d',
				'esc_html_e:1,2d',
				'esc_html_x:1,2c,3d',
				'esc_attr__:1,2d',
				'esc_attr_e:1,2d',
				'esc_attr_x:1,2c,3d',
				'_ex:1,2c,3d',
				'_n:1,2,4d',
				'_nx:1,2,4c,5d',
				'_n_noop:1,2,3d',
				'_nx_noop:1,2,3c,4d',
				'translate_nooped_plural:1,2c,3d'
			]
		} ) );
} );

