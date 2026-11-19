/* Análisis estático de JavaScript (ESLint 9, configuración plana) */
const js = require('@eslint/js');
const globals = require('globals');

/* Funciones y constantes que un script define y otro usa (se cargan con <script> en el navegador) */
const compartidas = {
  I18N: 'readonly', applyLang: 'readonly', getLang: 'readonly', lsGet: 'readonly', lsSet: 'readonly',
  HEADER_HTML: 'readonly', FOOTER_HTML: 'readonly',
  toast: 'readonly', newsSoon: 'readonly', submitForm: 'readonly', openCookie: 'readonly', acceptCookies: 'readonly',
  setLang: 'readonly', toggleLang: 'readonly', toggleDropdown: 'readonly',
};

module.exports = [
  js.configs.recommended,
  {
    files: ['assets/js/**/*.js', 'components/**/*.js', 'chat/**/*.js', 'Panel/**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'script',
      globals: { ...globals.browser, ...compartidas, module: 'readonly' },
    },
    rules: {
      'no-unused-vars': ['error', { vars: 'local', args: 'none', caughtErrors: 'none' }],
      'no-redeclare': ['error', { builtinGlobals: false }],
      eqeqeq: ['error', 'smart'],
      'no-implicit-globals': 'off',
      'no-empty': ['error', { allowEmptyCatch: true }],
    },
  },
  {
    files: ['tests/js/**/*.js', 'eslint.config.js'],
    languageOptions: { ecmaVersion: 2022, sourceType: 'commonjs', globals: { ...globals.node, ...globals.jest, ...globals.browser } },
  },
];
