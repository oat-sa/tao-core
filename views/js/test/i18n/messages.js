define(function () {
    return {
        serial: 'eafb7256bbb2e65d3cf1775a29417159',
        date: 1439814027,
        version: '3.1.0-sprint04',
        pluralForms: 'nplurals=4; plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 3;',
        p11nRules: function (n) {
            var index = Number((n==1) ? 0 : (n>=2 && n<=4) ? 1 : 3);

            if (!isFinite(index)) {
                return 0;
            }

            index = Math.floor(index);

            if (index < 0) {
                return 0;
            }

            if (index > 3) {
                return 3;
            }

            return index;
        },
        translations: {
            'mock-1': 'translation mock 1',
            'mock-2': 'translation mock 2',
            'params text %s': 'parameterized text translation %s',
            'params number %d': 'parameterized number translation %d',
            'params json %j': 'parameterized json translation %j',
            '%d day': {
                _plural: '%d days',
                _translations: [
                    '%d den',
                    '%d dni',
                    '%d dni',
                    '%d dni'
                ]
            }
        }
    };
});
