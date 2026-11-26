// Fichier de style servi par le site (public/css/tailwind.css), pour ne plus dépendre de cdn.tailwindcss.com.
// À régénérer après ajout de nouvelles classes Tailwind dans les vues : npm run css
module.exports = {
  content: {
    relative: true,
    files: ['../views/**/*.blade.php', '../../app/**/*.php', '../js/**/*.js'],
  },
  theme: { extend: {} },
  plugins: [],
};