const extendConfig = require('@oat-sa/e2e-runner/plugins/extendConfig');
const { downloadFile } = require('cypress-downloadfile/lib/addPlugin');
const fs = require('fs');

module.exports = (on, config) => {
   on('task', {
      downloadFile,
      readdir({ path }) {
         return new Promise((resolve, reject) => {
            fs.readdir(path, (err, files) => {
               if (err) {
                  reject(err);
               } else {
                  resolve(files);
               }
            });
         });
      },
      rmfile({ path }) {
         return new Promise((resolve, reject) => {
            fs.unlink(path, (err) => {
               if (err) {
                  reject(err);
               } else {
                  resolve(null);
               }
            });
         });
      }
   });

   on('before:browser:launch', (browser = {}, launchOptions) => {
      const downloadDirectory = config.downloadsFolder;

      if (browser.family === 'chromium') {
         launchOptions.preferences.default['download'] = {
            default_directory: downloadDirectory
         };
      }
      return launchOptions;
   });

   return extendConfig(config);
}
