const { src, dest, parallel, watch } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cssnano = require('gulp-cssnano');
const sourcemaps = require('gulp-sourcemaps');
const parcel = require('gulp-parcel');

const watchPaths = {
  adminScss: 'admin/scss/**/*.scss',
  woocommerceScss: 'woocommerce/scss/**/*.scss',
  woocommerceJs: 'woocommerce/js/**/*.js'
}

function styles() {
  return src('admin/scss/main.scss', {
    allowEmpty: true
  })
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(cssnano())
    .pipe(sourcemaps.write())
    .pipe(dest('admin/dist'));
}

function stylesWoocommerce() {
  return src('woocommerce/scss/main.scss', {
    allowEmpty: true
  })
    .pipe(sourcemaps.init())
    .pipe(sass().on('error', sass.logError))
    .pipe(cssnano())
    .pipe(sourcemaps.write())
    .pipe(dest('woocommerce/dist'));
}

function js() {
  return src('woocommerce/js/main.js')
    .pipe(parcel())
    .pipe(dest('woocommerce/dist'));
}

function startWatch() {
  watch(watchPaths.adminScss, styles);
  watch(watchPaths.woocommerceScss, stylesWoocommerce);
  // watch(watchPaths.woocommerceJs, js);
}

exports.default = parallel(styles, stylesWoocommerce, startWatch);
