// including plugins
var gulp = require('gulp'),
	sass = require("gulp-sass"),
	uglify = require("gulp-uglify"),
	uglifycss = require('gulp-uglifycss');

// task
gulp.task('default', function () {

	// SCSS
	gulp.src('./assets/scss/main.scss')
		.pipe(sass())
		.pipe(gulp.dest('./web/css/'));

	// Uglify CSS
	gulp.src('./web/css/*.css')
		.pipe(uglifycss({
			"maxLineLen": 80,
			"uglyComments": true
		}))
		.pipe(gulp.dest('./web/css/'));

	// JS
	gulp.src('./assets/js/*.js')
	//.pipe(uglify())
		.pipe(gulp.dest('./web/js/'));
});