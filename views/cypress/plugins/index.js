const extendConfig = require('@oat-sa/e2e-runner/plugins/extendConfig');
const { downloadFile } = require('cypress-downloadfile/lib/addPlugin');
const { getFiles, removeFile, readFile } = require('./downloads');

module.exports = (on, config) => {
   on('task', {
      downloadFile,
      removeDownload(file) {
         return removeFile(`${config.downloadsFolder}/${file}`);
      },
      readDownload(file) {
         return readFile(`${config.downloadsFolder}/${file}`, 'binary');
      },
      getDownloads() {
         return getFiles(config.downloadsFolder);
      }
   });

   // sets path for downloads
   on('before:browser:launch', (browser = {}, launchOptions) => {
      const downloadDirectory = config.downloadsFolder;

      if (browser.family === 'chromium' && browser.name !== 'electron') {
         launchOptions.preferences.default['download'] = {
            default_directory: downloadDirectory
         };
      }
      return launchOptions;
   });

   return extendConfig(config);
}
