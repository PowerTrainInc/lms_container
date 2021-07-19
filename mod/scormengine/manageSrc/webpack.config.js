const path = require('path');


module.exports = {
 mode: "development",
 entry: ["./src/main.js"],
 devtool: 'inline-source-map',
 output: {
   filename: 'js/app.bundle.js',
   path: path.resolve(__dirname, '../public/dist'), // base path where to send compiled assets
   publicPath: '/' // base path where referenced files will be look for
 },
 resolve: {
   extensions: ['*', '.js', '.jsx'],
   alias: {
     '@': path.resolve(__dirname, 'src') // shortcut to reference src folder from anywhere
   }
 },
 module: {
   rules: [
     { // config for es6 jsx
       test: /\.(js|jsx)$/,
       exclude: /node_modules/,
       use: {
         loader: "babel-loader"
       }
     }
   ]
 }
};