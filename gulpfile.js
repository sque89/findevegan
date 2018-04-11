var gulp = require('gulp');

gulp.task('copyImages', function() {
    return gulp.src('assets/images/*')
        .pipe(gulp.dest('public/images/misc'));
});

gulp.task('copyIconFont', function() {
    return gulp.src('assets/icons/iconic/font/fonts/open-iconic.woff')
        .pipe(gulp.dest('./public/fonts'));
});

gulp.task('default', ['copyImages', 'copyIconFont']);