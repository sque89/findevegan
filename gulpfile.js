var gulp = require('gulp');

gulp.task('copyImages', function() {
    return gulp.src('assets/images/*')
        .pipe(gulp.dest('public/images/misc'));
});

gulp.task('copyJquery', function() {
    return gulp.src('node_modules/jquery/dist/jquery.min.js')
        .pipe(gulp.dest('public/js/jquery'));
});

gulp.task('copyBootstrapJs', function() {
    return gulp.src('node_modules/bootstrap/dist/js/bootstrap.min.js')
        .pipe(gulp.dest('public/js/bootstrap'));
});

gulp.task('copyIconFont', function() {
    return gulp.src('assets/icons/iconic/font/fonts/open-iconic.woff')
        .pipe(gulp.dest('./public/fonts'));
});

gulp.task('default', ['copyImages', 'copyIconFont', 'copyBootstrapJs', 'copyJquery']);