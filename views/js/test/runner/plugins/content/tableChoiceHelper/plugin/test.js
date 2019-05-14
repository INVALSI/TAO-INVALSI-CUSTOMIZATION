/*
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; under version 2
 *  of the License (non-upgradable).
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *  Copyright (c) 2016 - 2019 (original work) Open Assessment Technologies S.A.
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
