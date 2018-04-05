var gulp = require('gulp');

gulp.task('copyImages', function() {
    return gulp.src('assets/images/*')
        .pipe(gulp.dest('public/images/misc'));
});

gulp.task('default', ['copyImages']);