'use strict';

let gulp         = require('gulp'),
	rename       = require('gulp-rename'),
	notify       = require('gulp-notify'),
	autoprefixer = require('gulp-autoprefixer'),
	sass         = require('gulp-sass'),
	uglify       = require('gulp-uglify-es').default,
	plumber      = require('gulp-plumber' );

gulp.task('jet-popup-frontend-css', () => {
	return gulp.src('./assets/scss/jet-popup-frontend.scss')
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

		.pipe(rename('jet-popup-frontend.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('jet-popup-admin-css', () => {
	return gulp.src('./assets/scss/jet-popup-admin.scss')
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

		.pipe(rename('jet-popup-admin.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('jet-popup-preview-css', () => {
	return gulp.src('./assets/scss/jet-popup-preview.scss')
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

		.pipe(rename('jet-popup-preview.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

gulp.task('jet-popup-editor-css', () => {
	return gulp.src('./assets/scss/jet-popup-editor.scss')
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

		.pipe(rename('jet-popup-editor.css'))
		.pipe(gulp.dest('./assets/css/'))
		.pipe(notify('Compile Sass Done!'));
});

// js
gulp.task( 'jet-popup-frontend-minify', () => {
	return gulp.src( './assets/js/jet-popup-frontend.js' )
		.pipe( uglify() )
		.pipe( rename({ extname: '.min.js' }) )
		.pipe( gulp.dest( './assets/js/') )
		.pipe( notify('js Minify Done!') );
});

gulp.task( 'jet-popup-admin-minify', () => {
	return gulp.src( './assets/js/jet-popup-admin.js' )
		.pipe( uglify() )
		.pipe( rename({ extname: '.min.js' }) )
		.pipe( gulp.dest( './assets/js/') )
		.pipe( notify('js Minify Done!') );
});

gulp.task( 'jet-popup-editor-minify', () => {
	return gulp.src( './assets/js/jet-popup-editor.js' )
		.pipe( uglify() )
		.pipe( rename({ extname: '.min.js' }) )
		.pipe( gulp.dest( './assets/js/') )
		.pipe( notify('js Minify Done!') );
});

//watch
gulp.task( 'watch', () => {
	gulp.watch( './assets/scss/**', gulp.series( ...[
		'jet-popup-frontend-css',
		'jet-popup-admin-css',
		'jet-popup-preview-css',
		'jet-popup-editor-css'
	] ) );

	gulp.watch( './assets/js/jet-popup-frontend.js', gulp.series( 'jet-popup-frontend-minify' ) );
	gulp.watch( './assets/js/jet-popup-admin.js', gulp.series( 'jet-popup-admin-minify' ) );
	gulp.watch( './assets/js/jet-popup-editor.js', gulp.series( 'jet-popup-editor-minify' ) );

});

