module.exports = {
  plugins: [
    require('autoprefixer')({
      browsers: [
        'Chrome >= 57',
        'Firefox >= 52',
        'Edge >= 14',
        'Explorer >= 11',
        'iOS >= 9',
        'Safari >= 8',
        'Android >= 4',
        'Opera >= 43'
      ]
    }),
    require('postcss-clean')({
      rebase: false,
      level: {
        1: {
          specialComments: 0
        }
      }
    })
  ]
}

