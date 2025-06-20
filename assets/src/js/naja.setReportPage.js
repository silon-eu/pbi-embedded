import Naja from 'naja';
document.addEventListener('DOMContentLoaded', function() {

    Naja.addEventListener('before', (event) => {
        if (event.detail.url.includes('do=changePage')) {
            const placeholder = document.getElementById("report-placeholder");
            if (placeholder) {
                placeholder.style.display = "block";
            }
        }
    });

    Naja.addEventListener('success', async function (event) {
        if (event.detail.payload) {
            if (report !== undefined && 'activePageData' in event.detail.payload) {
                let activePageData = event.detail.payload.activePageData;
                let reportActivePage = await report.getActivePage();
                let newPage = await report.getPageByName(activePageData.page);

                // set filters first
                if (activePageData.filters !== null && activePageData.filters !== '') {
                    let pageFilter = JSON.parse(activePageData.filters);
                    await newPage.updateFilters(models.FiltersOperations.ReplaceAll, pageFilter);
                }

                // if needed change page
                if (reportActivePage.name !== newPage.name) {
                    await report.setPage(newPage.name);
                }

                // set slicer states
                if (activePageData.slicers !== null && activePageData.slicers !== '') {
                    let slicerStates = JSON.parse(activePageData.slicers);
                    await setDefaultSlicersForPage(slicerStates, newPage.name);
                }
            }
        }
    });

});