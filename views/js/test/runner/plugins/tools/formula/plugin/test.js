/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Christophe Noël <christophe@taotesting.com>
 */
define([
    'lodash',
    'helpers',
    'taoTests/runner/runner',
    'taoQtiTest/test/runner/mocks/providerMock',
    'taoInvalsi/runner/plugins/tools/formula/plugin'
], function(_, helpers, runnerFactory, providerMockFactory, pluginFactory) {
    'use strict';

    var pluginApi;
    var providerName = 'mock';

    runnerFactory.registerProvider(providerName, providerMockFactory());

    /**
     * Generic tests
     */
    QUnit.module('pluginFactory');

    QUnit.test('module', function(assert) {
        var runner = runnerFactory(providerName);

        QUnit.expect(3);

        assert.equal(typeof pluginFactory, 'function', 'The pluginFactory module exposes a function');
        assert.equal(typeof pluginFactory(runner), 'object', 'The plugin factory produces an instance');
        assert.notStrictEqual(pluginFactory(runner), pluginFactory(runner), 'The plugin factory provides a different instance on each call');
    });

    pluginApi = [
        { name : 'init',            title : 'init' },
        { name : 'render',          title : 'render' },
        { name : 'finish',          title : 'finish' },
        { name : 'destroy',         title : 'destroy' },
        { name : 'trigger',         title : 'trigger' },
        { name : 'getTestRunner',   title : 'getTestRunner' },
        { name : 'getAreaBroker',   title : 'getAreaBroker' },
        { name : 'getConfig',       title : 'getConfig' },
        { name : 'setConfig',       title : 'setConfig' },
        { name : 'getState',        title : 'getState' },
        { name : 'setState',        title : 'setState' },
        { name : 'show',            title : 'show' },
        { name : 'hide',            title : 'hide' },
        { name : 'enable',          title : 'enable' },
        { name : 'disable',         title : 'disable' }
    ];

    QUnit.module('Plugin API');

    QUnit
        .cases(pluginApi)
        .test('plugin API ', function(data, assert) {
            var runner = runnerFactory(providerName);
            var plugin = pluginFactory(runner);
            QUnit.expect(1);
            assert.equal(typeof plugin[data.name], 'function', 'The pluginFactory instances expose a "' + data.name + '" function');
        });


    /**
     * Button
     */
    QUnit.module('Button');

    QUnit.asyncTest('render/enable/disable/destroy', function (assert) {
        var runner      = runnerFactory(providerName),
            areaBroker  = runner.getAreaBroker(),
            plugin      = pluginFactory(runner, areaBroker),
            toolbox     = areaBroker.getToolbox(),
            $container  = areaBroker.getToolboxArea(),
            buttonSelector = '[data-control="formula"]',
            $button;

        QUnit.expect(12);

        plugin.init()
            .then(function () {
                assert.equal(plugin.getState('init'), true, 'The plugin has been initialized');

                // toolbox rendering
                toolbox.render($container);

                $button = $container.find(buttonSelector);

                assert.equal($button.length, 1, 'The plugin button has been inserted in the right place');
                assert.equal($button.hasClass('disabled'), true, 'The button has been rendered disabled');

                // Plugin rendering
                return plugin.render().then(function () {
                    assert.equal(plugin.getState('ready'), true, 'The plugin is ready');

                    // enabling
                    return plugin.enable().then(function () {
                        assert.equal(plugin.getState('enabled'), true, 'The plugin is enabled');
                        assert.equal($button.hasClass('disabled'), false, 'The button is not disabled');

                        // disabling
                        return plugin.disable().then(function () {
                            assert.equal(plugin.getState('enabled'), false, 'The plugin is disabled');
                            assert.equal($button.hasClass('disabled'), true, 'The button is disabled');
                            assert.equal($button.hasClass('active'), false, 'The button is turned off');

                            // plugin destroying
                            return plugin.destroy().then(function () {
                                $button = $container.find(buttonSelector);

                                assert.equal(plugin.getState('init'), false, 'The plugin is destroyed');
                                assert.equal($button.length, 1, 'The plugin button has not been destroyed by the plugin');

                                // toolbox destroying
                                toolbox.destroy();

                                $button = $container.find(buttonSelector);

                                assert.equal($button.length, 0, 'The button has been removed');
                                QUnit.start();
                            });
                        });
                    });
                });
            })
            .catch(function (err) {
                assert.ok(false, 'Unexpected failure : ' + err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('show/hide', function (assert) {
        var runner      = runnerFactory(providerName),
            areaBroker  = runner.getAreaBroker(),
            plugin      = pluginFactory(runner, areaBroker),
            toolbox     = areaBroker.getToolbox(),
            $container  = areaBroker.getToolboxArea(),
            buttonSelector = '[data-control="formula"]',
            $button;

        QUnit.expect(7);

        plugin.init()
            .then(function () {
                assert.equal(plugin.getState('init'), true, 'The plugin has been initialized');

                // toolbox rendering
                toolbox.render($container);

                $button = $container.find(buttonSelector);

                plugin.setState('visible', true);

                assert.equal($button.length, 1, 'The plugin button has been inserted in the right place');
                assert.equal(plugin.getState('visible'), true, 'The plugin is visible');

                // hiding
                return plugin.hide().then(function () {
                    assert.equal(plugin.getState('visible'), false, 'The plugin is not visible');
                    assert.equal($button.css('display'), 'none', 'The plugin element is hidden');

                    // showing
                    return plugin.show().then(function () {
                        assert.equal(plugin.getState('visible'), true, 'The plugin is visible');
                        assert.notEqual($button.css('display'), 'none', 'The plugin element is visible');

                        QUnit.start();
                    });
                });
            })
            .catch(function (err) {
                assert.ok(false, 'Unexpected failure : ' + err.message);
                QUnit.start();
            });
    });

    QUnit.asyncTest('runner events: loaditem / unloaditem', function(assert) {
        var runner      = runnerFactory(providerName),
            areaBroker  = runner.getAreaBroker(),
            plugin      = pluginFactory(runner, runner.getAreaBroker()),
            toolbox     = areaBroker.getToolbox(),
            $container  = areaBroker.getToolboxArea(),
            buttonSelector = '[data-control="formula"]',
            $button;

        runner.setTestContext({
            options: {
                formula: true
            }
        });

        runner.setTestData({
            config: {
                plugins: {
                    formula: {
                        documentUrl: 'url.pdf'
                    }
                }
            }
        });

        QUnit.expect(4);

        plugin.init()
            .then(function() {
                toolbox.render($container);

                $button = $container.find(buttonSelector);

                assert.equal($button.length, 1, 'The plugin button has been inserted in the right place');

                runner.trigger('loaditem');

                assert.notEqual($button.css('display'), 'none', 'The plugin button is visible');

                runner.trigger('unloaditem');

                assert.notEqual($button.css('display'), 'none', 'The plugin button is still visible');
                assert.equal($button.hasClass('disabled'), true, 'The button is disabled');

                QUnit.start();
            })
            .catch(function(err) {
                assert.ok(false, 'Error in init method: ' + err);
                QUnit.start();
            });
    });

    QUnit.asyncTest('runner events: renderitem', function(assert) {
        var runner      = runnerFactory(providerName),
            plugin      = pluginFactory(runner, runner.getAreaBroker()),
            areaBroker  = runner.getAreaBroker(),
            toolbox     = areaBroker.getToolbox(),
            $container  = areaBroker.getToolboxArea(),
            buttonSelector = '[data-control="formula"]',
            $button;

        runner.setTestContext({
            options: {
                formula: true
            }
        });

        QUnit.expect(3);

        plugin.init()
            .then(function() {
                toolbox.render($container);

                $button = $container.find(buttonSelector);

                assert.equal($button.length, 1, 'The plugin button has been inserted in the right place');

                runner.trigger('renderitem');

                assert.notEqual($button.css('display'), 'none', 'The plugin button is visible');
                assert.equal($button.hasClass('disabled'), false, 'The button is not disabled');

                QUnit.start();
            })
            .catch(function(err) {
                assert.ok(false, 'An error has occurred: ' + err);
                QUnit.start();
            });
    });

    QUnit
        .cases([
            {
                title: 'formula category set',
                qtiCategories: { formula: true },
                pluginOptions: { documentUrl: 'formula.pdf', documentUrlL10: 'formula_L10.pdf', documentUrlL13: 'formula_L13.pdf' },
                document: 'formula.pdf'
            }, {
                title: 'formula category set, no url',
                qtiCategories: { formula: true }
            }, {
                title: 'formulaL10 category set, url',
                qtiCategories: { formulaL10: true },
                pluginOptions: { documentUrl: 'formula.pdf', documentUrlL10: 'formula_L10.pdf', documentUrlL13: 'formula_L13.pdf' },
                document: 'formula_L10.pdf'
            }, {
                title: 'formulaL10 category set, no url',
                qtiCategories: { formulaL10: true }
            }, {
                title: 'formulaL13 category set',
                qtiCategories: { formulaL13: true },
                pluginOptions: { documentUrl: 'formula.pdf', documentUrlL10: 'formula_L10.pdf', documentUrlL13: 'formula_L13.pdf' },
                document: 'formula_L13.pdf'
            }, {
                title: 'formulaL13 category set, no url',
                qtiCategories: { formulaL13: true }
            }
        ])
        .asyncTest('', function(data, assert) {
            var runner      = runnerFactory(providerName),
                plugin      = pluginFactory(runner, runner.getAreaBroker()),
                areaBroker  = runner.getAreaBroker(),
                toolbox     = areaBroker.getToolbox(),
                $container  = areaBroker.getToolboxArea(),
                buttonSelector = '[data-control="formula"]',
                $button,
                loadedDocument;

            QUnit.expect(4);

            runner.setTestContext({
                options: data.qtiCategories
            });

            runner.setTestData({
                config: {
                    plugins: {
                        formula: data.pluginOptions
                    }
                }
            });

            runner
                .on('tool-documentViewer', function(options) {
                    loadedDocument = options.document;
                })
                .after('tool-formula', function() {
                    assert.equal(loadedDocument, data.document, 'correct document has been loaded');
                    QUnit.start();
                });

            plugin.init()
                .then(function() {
                    toolbox.render($container);

                    $button = $container.find(buttonSelector);

                    runner.trigger('renderitem');

                    assert.equal($button.length, 1, 'The plugin button has been inserted in the right place');
                    assert.notEqual($button.css('display'), 'none', 'The plugin button is visible');
                    assert.equal($button.hasClass('disabled'), false, 'The button is not disabled');

                    runner.trigger('tool-formula'); // simulate click
                })
                .catch(function(err) {
                    assert.ok(false, 'An error has occurred: ' + err);
                    QUnit.start();
                });
        });

});
