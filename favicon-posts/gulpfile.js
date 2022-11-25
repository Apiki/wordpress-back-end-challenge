const gulp = require('gulp');
const sass = require('gulp-sass');
const sassGlob = require('gulp-sass-glob');

var uglify = require('gulp-uglify-es').default;
var rename   = require('gulp-rename');

// Dev Paths
var js_dev   = "./assets/dev_js/**/*.js";
// Dist Paths
var js_dist  = "./assets/js";

function scripts(){
    return(
    gulp.src(js_dev)
        .pipe(uglify())
        .pipe(rename({suffix: ".min"}))
        .pipe(gulp.dest(js_dist))
    )
}

function compile(){
	return(
    gulp.src('./assets/scss/*.scss')
        .pipe(sassGlob())
		.pipe(sass({outputStyle:'compressed'}))
		.pipe(gulp.dest('./assets/css/'))
	)
}
exports.compile = compile;

function watchfiles(){
	gulp.watch('./assets/scss/**', compile);
    gulp.watch('./assets/dev_js/**', scripts);
}
const watch = gulp.parallel(watchfiles)
exports.watch = watch;
