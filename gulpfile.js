const { src, dest, watch, series } = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const sourcemaps = require('gulp-sourcemaps');

// Compile SCSS -> CSS
function scssTask() {
    return src('./assets/styles/main.scss', { allowEmpty: true }) // ✅ chemin clair
        .pipe(sourcemaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCSS())
        .pipe(rename({ suffix: '.min' }))
        .pipe(sourcemaps.write('.'))
        .pipe(dest('./public/build')); // ✅ sortie correcte
}

// Compile JS -> minifié
function jsTask() {
    return src('./assets/js/*.js')
        .pipe(concat('main.min.js'))
        .pipe(uglify())
        .pipe(dest('./public/build')); // ✅ sortie correcte
}

// Watch SCSS & JS
function watchTask() {
    watch('./assets/styles/**/*.scss', scssTask);
    watch('./assets/js/**/*.js', jsTask);
}

// Tasks
exports.default = series(scssTask, jsTask, watchTask);
