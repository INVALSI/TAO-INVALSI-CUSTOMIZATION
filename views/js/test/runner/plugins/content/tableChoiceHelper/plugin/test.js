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
 * @author Dieter Raber <dieter@taotesting.com>
 */
define([
    'jquery',
    'taoTests/runner/runner',
    'taoQtiTest/test/runner/mocks/providerMock',
    'taoInvalsi/runner/plugins/content/tableChoiceHelper'
], function($, runnerFactory, providerMock, tableChoiceHelperFactory) {
    'use strict';

    var providerName = 'mock';
    runnerFactory.registerProvider(providerName, providerMock());

    QUnit.module('tableChoiceHelperFactory');

    QUnit.test('module', function(assert) {
        assert.ok(typeof tableChoiceHelperFactory === 'function', 'Module exposes a function');
    });

    QUnit.module('Table Choice Helper');

    QUnit.asyncTest('Run Table Choice Helper', function(assert) {
        var runner            = runnerFactory(providerName);
        var areaBroker        = runner.getAreaBroker();
        var tableChoiceHelper = tableChoiceHelperFactory(runner, areaBroker);

        runner.setTestContext({
            options: {
                tableChoiceHelper: true
            }
        })
        .on('error', function(err){
            console.log(err);
        })

        areaBroker.getContentArea().append($('#item'));

        tableChoiceHelper.init()
            .then(function() {
                runner.after('renderitem', function() {
                    assert.ok(areaBroker.getContentArea().find('#ia-1').hasClass('first-choice-interaction'), '1st item has a marker');
                    assert.ok(!areaBroker.getContentArea().find('#ia-2').hasClass('first-choice-interaction'), '2nd item has no marker');
                    assert.ok(!areaBroker.getContentArea().find('#ia-3').hasClass('first-choice-interaction'), '3rd item has no marker');
                    assert.ok(!areaBroker.getContentArea().find('#ia-4').hasClass('first-choice-interaction'), '4th item has no marker');
                })
                runner.trigger('renderitem');
                
                QUnit.start();
            });
    });

});
