const { src, dest, parallel, watch } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cssnano = require('gulp-cssnano');
const sourcemaps = require('gulp-sourcemaps');

function styles() {
  return src('admin/css/main.scss', {
    allowEmpty: true
  })
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(cssnano())
    .pipe(sourcemaps.write())
    .pipe(dest('admin/css'));
}

function startWatch() {
  watch('admin/css/**/*.scss', styles);
}

exports.default = parallel(styles, startWatch);
