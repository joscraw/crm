'use strict';

import $ from 'jquery';
import swal from "sweetalert2";
import RecordForm from './RecordForm';

class ReportFormModal {

    /**
     * @param globalEventDispatcher
     * @param portalInternalIdentifier
     */
    constructor(globalEventDispatcher, portalInternalIdentifier) {
        this.globalEventDispatcher = globalEventDispatcher;
        this.portalInternalIdentifier = portalInternalIdentifier;

        this.render();
    }

    render() {

        (async function backAndForth() {
            debugger;
            const steps = ['1', '2', '3'];
            const values = [];
            let currentStep;

            swal.setDefaults({
                confirmButtonText: 'Forward',
                cancelButtonText: 'Back',
                progressSteps: steps,
                input: 'text',
                allowOutsideClick: true
            })

            for (currentStep = 0; currentStep < 3;) {
                debugger;
                const result = await swal({
                    title: 'Question ' + steps[currentStep],
                    inputValue: values[currentStep] ? values[currentStep] : '',
                    showCancelButton: currentStep > 0,
                    currentProgressStep: currentStep,
                    allowOutsideClick: true
                });

                if (result.value) {
                    debugger;
                    values[currentStep] = result.value
                    currentStep++;
                    if (currentStep === 3) {
                        swal.resetDefaults();
                        swal(JSON.stringify(values))
                        break
                    }
                } else if (result.dismiss === 'cancel') {
                    debugger;
                    currentStep--;
                }
            }
        })()




      /*  swal({
            title: `Create Report`,
            showConfirmButton: false,
            html: ReportFormModal.markup(),
            customClass: "swal2-modal--left-align"
        });
*/
        /*new RecordForm($('#js-create-record-modal-container'), this.globalEventDispatcher, this.portalInternalIdentifier, this.customObjectInternalName);*/
    }

    static markup() {
        return `
      <div id="js-create-record-modal-container"></div>
    `;
    }
}

export default ReportFormModal;