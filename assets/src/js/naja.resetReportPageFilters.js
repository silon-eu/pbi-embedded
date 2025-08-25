import Naja from 'naja';
document.addEventListener('DOMContentLoaded', function() {

    /*Naja.addEventListener('before', (event) => {
        if (event.detail.url.includes('do=resetFilters')) {
            const placeholder = document.getElementById("report-placeholder");
            if (placeholder) {
                placeholder.style.display = "block";
            }
        }
    });*/

    Naja.addEventListener('success', async function (event) {
        if (event.detail.payload && event.detail.payload.resetFilters === true) {
            console.log('Resetting report page filters...');
            let activePageData = event.detail.payload.activePageData;
            if (report !== undefined && 'activePageData' in event.detail.payload) {
                let page = await report.getPageByName(activePageData.page);

                // set filters first
                /*if (activePageData.filters !== null && activePageData.filters !== '') {
                    let pageFilter = JSON.parse(activePageData.filters);
                    await page.updateFilters(models.FiltersOperations.ReplaceAll, pageFilter);
                }*/

                // set slicer states
                if (activePageData.slicers !== null && activePageData.slicers !== '') {
                    let slicerStates = JSON.parse(activePageData.slicers);
                    await setDefaultSlicersForPage(slicerStates, page.name, true);
                }
            }
        }
    });

});