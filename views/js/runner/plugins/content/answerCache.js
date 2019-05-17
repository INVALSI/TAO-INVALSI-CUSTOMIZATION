/*
 * ***********************************************************************
 * INVALSI CONFIDENTIAL
 * __________________
 *
 *  All Rights Reserved.
 *
 *  NOTICE:  All information contained herein is, and remains
 *  the property of INVALSI and its suppliers, if any.  The
 *  intellectual and technical concepts contained herein are
 *  proprietary to INVALSI and its suppliers, and are protected
 *  by trade secret or copyright law.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from INVALSI.
 *  ************************************************************************
 */
/**
 * Plugin that puts in cache the current item's state upon move.
 * After a page refresh, the cached state is loaded to restore the previously set state.
 * The cache is cleared when the item attempt is finished.
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'taoTests/runner/plugin'
], function (pluginFactory) {
    'use strict';

    /**
     * Returns the configured plugin
     */
    return pluginFactory({

        name: 'answerCache',

        /**
         * Initialize the plugin (called during runner's init)
         */
        init: function init() {
            var self = this;
            var testRunner = this.getTestRunner();

            return testRunner.getPluginStore(this.getName()).then(function (store) {
                self.storage = store;

                testRunner
                    .after('renderitem', function () {
                        var itemRunner = testRunner.itemRunner;
                        var testContext = testRunner.getTestContext();
                        var key = testContext.itemIdentifier;

                        if (itemRunner) {
                            itemRunner.on('responsechange', function () {
                                self.storage.setItem(key, itemRunner.getState());
                            });
                            return self.storage.getItem(key)
                                .then(function (state) {
                                    if (state) {
                                        itemRunner.setState(state);
                                    }
                                });
                        }
                    })
                    .on('unloaditem', function () {
                        // the store should not be cleared after a pause
                        // otherwise the response won't be restored after resume
                        if (!testRunner.getState('closedOrSuspended')) {
                            return self.storage.clear();
                        }
                    });
            });
        }
    });
});
