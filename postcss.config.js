const path = require('path');
const fs   = require('fs');
const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
  parser: 'postcss-scss',
  plugins: [
    require('postcss-import')({
      // Resolve SCSS partial imports: 'variables' → '_variables.scss'
      resolve(id, basedir) {
        const candidates = [
          path.resolve(basedir, `_${id}.scss`),
          path.resolve(basedir, `${id}.scss`),
          path.resolve(basedir, `_${id}.css`),
          path.resolve(basedir, id),
        ];
        for (const candidate of candidates) {
          if (fs.existsSync(candidate)) return candidate;
        }
        return id;
      },
    }),
    require('postcss-mixins'),
    require('postcss-nested'),
    require('tailwindcss'),
    require('autoprefixer'),
    ...(isProduction ? [require('cssnano')({ preset: 'default' })] : []),
  ],
};
