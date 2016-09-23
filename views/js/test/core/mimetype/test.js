define(['core/mimetype'], function(mimeType){
    'use strict';

    QUnit.test('protoype', function(assert){
       assert.ok(typeof mimeType === 'object', 'The module mimeType expose an object');
       assert.ok(typeof mimeType.getFileType === 'function', 'The module mimeType has a getFileType method');
       assert.ok(typeof mimeType.getResourceType === 'function', 'The module mimeType has a getResourceType method');
    });

    QUnit.test('getFileType', function(assert){
        assert.equal(mimeType.getFileType({mime : 'application/ogg'}), 'video', 'Ogg files are video');
        assert.equal(mimeType.getFileType({mime : 'audio/mp3'}), 'audio', 'Mp3 files are audio');
        assert.equal(mimeType.getFileType({mime : 'text/css'}), 'css', 'Css mime type');
        assert.equal(mimeType.getFileType({name : 'style.css'}), 'css', 'Css extension');
        assert.equal(mimeType.getFileType({mime : 'text/plain'}), 'text', 'Text mime type');
    });

    QUnit.test('getCategory', function(assert){
        assert.equal(mimeType.getCategory('video'), 'media', 'video files are media');
        assert.equal(mimeType.getCategory('css'), 'sources', 'css files are sources');
    });

    var resources = [
        { url: '/tao/views/js/test/core/mimetype/samples/audio.mp3', type: 'audio/mpeg', error: false, title : 'MP3' },
        { url: '/tao/views/js/test/core/mimetype/samples/video.mp4', type: 'video/mp4', error: false, title : 'MP4' },
        { url: '/tao/views/js/test/core/mimetype/samples/unknown', type: null, error: true, title : 'Unknown resource' }
    ];

    QUnit
        .cases(resources)
        .asyncTest('getResourceType ', function(data, assert) {
            mimeType.getResourceType(data.url, function(err, type) {
                assert.equal(!!err, data.error, 'The callback accept an error');
                if (!data.error) {
                    assert.equal(type, data.type, 'The callback received the correct MIME type');
                }
                QUnit.start();
            });
        });

    var mimetypes = [
        //simple correct mime typed files
        { filename: 'filename.zip', mime: 'application/zip', equals: 'application/zip', title : 'application/zip zip'},
        { filename: 'filename.jpg', mime: 'image/jpeg', equals: 'image/jpeg', title : 'image/jpeg jpg'},
        //if the File has a generic typed mime, the mime type resolution will be based on the file extension
        { filename: 'filename.txt', mime: 'invalid/octet-stream', equals: 'text/plain', title : 'invalid/octet-stream text'},
        { filename: 'filename.pdf', mime: 'invalid/octet-stream', equals: 'application/pdf', title : 'invalid/octet-stream pdf'},
        { filename: 'filename.pdf', mime: 'application/octet-stream', equals: 'application/pdf', title : 'application/octet-stream pdf'},
        { filename: 'filename.pdf', mime: 'application/force-download', equals: 'application/pdf', title : 'application/force-download pdf'},
        //if the file extension is of an unknown mime, the returned value defaults to the mimetype of the File
        { filename: 'filename.bz2', mime: 'application/octet-stream', equals: 'application/octet-stream', title : 'application/octet-stream bz2'}
    ];

    QUnit
        .cases(mimetypes)
        .test('getMimeType', function(data, assert) {
            //create a file mock
            var file = {
                name : data.filename,
                type : data.mime,
            };
            assert.equal(mimeType.getMimeType(file), data.equals, data.title);
        });
});
