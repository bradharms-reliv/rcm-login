var RcmResetPassword = function (instanceId, translate) {
    /**
     * Always refers to this object unlike the 'this' JS variable;
     * @type {RcmDistributorApp}
     */
    var me = this;

    /**
     * Plugin container div jQuery object
     * @type {Object}
     */
    var container = rcm.getPluginContainer(instanceId);

    var hearSelect = container.find('select[name=howHearAboutReliv]');
    var howWrap = container.find('.howHearAboutRelivExplainWrap');
    var explainLabel = container.find('.explainDesc');

    this.showHideHowHear = function () {
        switch (hearSelect.val()) {
            case 'distributor':
                explainLabel.html(translate['distributorName']);
                howWrap.show();
                break;
            case 'other':
                explainLabel.html(translate['explain']);
                howWrap.show();
                break;
            default:
                howWrap.hide();
                explainLabel.html(23142134)
        }
    };
    hearSelect.change(me.showHideHowHear);

    //Ensure explain box shows when form re-displays invalid entries
    this.showHideHowHear();
};