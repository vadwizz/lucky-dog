"use strict"

const {src, dest } = require("gulp")
const gulp = require('gulp')
const autoprefixer = require("gulp-autoprefixer")
const cssbeautify = require("gulp-cssbeautify");
const removeComments = require('gulp-strip-css-comments');
const rename = require("gulp-rename");
const rigger = require("gulp-rigger")
const sass = require("gulp-sass")(require('sass'));
const cssnano = require("gulp-cssnano");
const uglify = require("gulp-uglify");
const plumber = require("gulp-plumber");
const panini = require("panini");
const imagemin = require("gulp-imagemin");
const notify = require("gulp-notify");
//const del = require("del");

const path = {
    build: {
        css: "web/themes/custom/LuckyDog/css",
        js: "web/themes/custom/LuckyDog/js",
        img: "web/themes/custom/LuckyDog/images"
    },
    src: {
        css: "web/themes/custom/LuckyDog/sass/*.scss",
        js: "web/themes/custom/LuckyDog/lib/*.js",
        img: "web/themes/custom/LuckyDog/images/*"
    },
    watch: {
        css: "web/themes/custom/LuckyDog/sass/**/*.scss",
        js: "web/themes/custom/LuckyDog/lib/*.js",
    }
}

function css(){
    return src(path.src.css, {base: "web/themes/custom/LuckyDog/sass/"})
        .pipe(plumber({
            errorHandler : function(err) {
                notify.onError({
                    title:    "SCSS Error",
                    message:  "Error: <%= error.message %>"
                })(err);
                this.emit('end');}
            }))
        .pipe(sass())
        .pipe(autoprefixer())
        .pipe(cssbeautify())
        .pipe(dest(path.build.css))
        .pipe(cssnano({
            zindex : false,
            discardComments: {
                removeAll : true
            }
        }))
        .pipe(rename({
            suffix : ".min",
            extname: ".css"
        }))
        .pipe(dest(path.build.css))
}

function js(){
    return src(path.src.js, {base: "web/themes/custom/LuckyDog/lib/"})
        .pipe(plumber({
            errorHandler : function(err) {
                notify.onError({
                    title:    "SCSS Error",
                    message:  "Error: <%= error.message %>"
                })(err);
                this.emit('end');}
            }))
        .pipe(rigger())
        .pipe(dest(path.build.js))
        .pipe(uglify())
        .pipe(rename({
            suffix : ".min",
            extname: ".js"
        }))
        .pipe(dest(path.build.js))
}

function watchFiles(){
    gulp.watch([path.watch.css], css)
    gulp.watch([path.watch.js], js)
}

const build = gulp.series(css, gulp.parallel(js))
const watch = gulp.parallel(build, watchFiles)

exports.css = css
exports.js = js
exports.build = build
exports.watch = watch
exports.default = watch
