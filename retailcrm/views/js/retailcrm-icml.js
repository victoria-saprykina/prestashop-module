/**
 * MIT License
 *
 * Copyright (c) 2020 DIGITAL RETAIL TECHNOLOGIES SL
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    DIGITAL RETAIL TECHNOLOGIES SL <mail@simlachat.com>
 *  @copyright 2020 DIGITAL RETAIL TECHNOLOGIES SL
 *  @license   https://opensource.org/licenses/MIT  The MIT License
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */
$(function () {
    function RetailcrmIcmlForm(tabController) {
        this.submitButton = $('button[id="generate-icml-submit"]').get(0);
        this.updateButton = $('button[id="update-icml-submit"]').get(0);
        this.form = $(this.submitButton).closest('form').get(0);
        this.icmlField = $(this.form).find('input[name="RETAILCRM_RUN_JOB"]').get(0);

        if (typeof this.form === 'undefined') {
            return false;
        }

        this.submitAction = this.submitAction.bind(this);
        this.updateAction = this.updateAction.bind(this);
        this.setLoading = this.setLoading.bind(this);
        this.tabController = tabController;

        $(this.submitButton).click(this.submitAction);
        $(this.updateButton).click(this.updateAction);
    }

    RetailcrmIcmlForm.prototype.submitAction = function (event) {
        event.preventDefault();

        this.setLoading(true);
        this.tabController.storeTabInAction(this.form);

        $(this.icmlField).val('RetailcrmIcmlEvent');
        $(this.form).submit();
    };

    RetailcrmIcmlForm.prototype.updateAction = function (event) {
        event.preventDefault();

        this.setLoading(true);
        this.tabController.storeTabInAction(this.form);

        $(this.icmlField).val('RetailcrmIcmlUpdateUrlEvent');
        $(this.form).submit();
    };

    RetailcrmIcmlForm.prototype.setLoading = function (loading) {
        var loaderId = 'retailcrm-loading-fade',
            indicator = $('#' + loaderId);

        if (indicator.length === 0) {
            $('body').append(`
            <div id="${loaderId}">
                <div id="retailcrm-loader"></div>
            </div>
            `.trim());

            indicator = $('#' + loaderId);
        }

        indicator.css('visibility', (loading ? 'visible' : 'hidden'));
    };

    window.RetailcrmIcmlForm = RetailcrmIcmlForm;
});
