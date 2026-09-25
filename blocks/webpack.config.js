const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const sharedAliases = require('../../tsjippy-shared-functionality/js/webpack.aliases'); 
const externals = require('../../tsjippy-shared-functionality/js/webpack.externals');

module.exports = {
	...defaultConfig[0],
	resolve: {
		...defaultConfig[0].resolve,
		alias: {
			...(defaultConfig[0].resolve?.alias || {}),

			...sharedAliases,
		},
	},
    externalsType: 'module',
    externals,
};