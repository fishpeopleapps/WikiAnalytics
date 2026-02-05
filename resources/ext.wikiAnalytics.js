mw.loader.using( 'ext.wikiAnalytics' ).then( () => {

	const charts = new Map();

	const analyticsForm = document.createElement( 'div' );
	analyticsForm.id = 'analytics-form';

	// Fieldset
	const fieldset = document.createElement( 'fieldset' );

	const legend = document.createElement( 'legend' );
	legend.textContent = 'Analytics Range';
	fieldset.appendChild( legend );

// ---- Range dropdown
	const rangeLabel = document.createElement( 'label' );
	rangeLabel.setAttribute( 'for', 'analytics-range' );
	rangeLabel.textContent = 'Range:';

	const rangeSelect = document.createElement( 'select' );
	rangeSelect.id = 'analytics-range';
	rangeSelect.name = 'range';

	fieldset.appendChild( rangeLabel );
	fieldset.appendChild( rangeSelect );

    const ranges = mw.config.get( 'wgWikiAnalyticsRanges' );
    if ( ranges ) {
        Object.entries( ranges ).forEach( ( [ value, label ] ) => {
            const option = document.createElement( 'option' );
            option.value = value;
            option.textContent = label;
            rangeSelect.appendChild( option );
        } );
    }
	

// ---- flatpickr date picker
	const dateFieldset = document.createElement( 'fieldset' );
	dateFieldset.id = 'analytics-custom-dates';
	dateFieldset.style.display = 'none'; 
	const dateLegend = document.createElement( 'legend' );

	const startDate = document.createElement( 'input' );
    startDate.type = 'text';
    startDate.id = 'analytics-start-date';
    startDate.placeholder = 'Start date';

    const endDate = document.createElement( 'input' );
    endDate.type = 'text';
    endDate.id = 'analytics-end-date';
    endDate.placeholder = 'End date';


	dateFieldset.appendChild( dateLegend );
	dateFieldset.appendChild( startDate );
	dateFieldset.appendChild( endDate );

	fieldset.appendChild( dateFieldset );

// ---- Compare Checkbox
	const compareFieldset = document.createElement( 'fieldset' );

	const compareLabel = document.createElement( 'label' );
	compareLabel.setAttribute( 'for', 'analytics-compare' );

	const compareCheckbox = document.createElement( 'input' );
	compareCheckbox.type = 'checkbox';
	compareCheckbox.id = 'analytics-compare';
	compareCheckbox.name = 'compare';

	compareLabel.appendChild( compareCheckbox );
	compareLabel.appendChild(
		document.createTextNode( ' Compare against previous year' )
	);

	compareFieldset.appendChild( compareLabel );
	fieldset.appendChild( compareFieldset );

	// ---- Apply button
	const applyButton = document.createElement( 'button' );
	applyButton.id = 'analytics-apply-button';
	applyButton.type = 'button';
	applyButton.textContent = 'Apply';

	fieldset.appendChild( applyButton );

// ---- Graph placeholder
	const resultsHeading = document.createElement( 'h3' );
	resultsHeading.textContent = 'Results';
	fieldset.appendChild( resultsHeading );

	const graphGrid = document.createElement( 'div' );
	graphGrid.className = 'analytics-grid';
	fieldset.appendChild( graphGrid );

	const graphs = [];

	for ( let i = 1; i <= 36; i++ ) {
		const graph = createGraphCard( `Metric ${ i }` );
		graphGrid.appendChild( graph.fieldset );
		graphs.push( graph );
	}



	function renderChart( canvas, labels, data, compareData = null ) {
		const ctx = canvas.getContext( '2d' );

		// Dummy Data to ensure its working
		const datasets = [
			{
				label: 'Page Views',
				data,
				borderWidth: 2,
				tension: 0.3
			}
		];
		if ( compareData ) {
			datasets.push( {
				label: 'Previous Year',
				data: compareData,
				borderWidth: 2,
				borderDash: [ 6, 4 ],
				tension: 0.3
			} );
		}
		if ( charts.has( canvas ) ) {
			charts.get( canvas ).destroy();
		}

		const chart = new Chart( ctx, {
			type: 'line',
			data: { labels, datasets },
			options: {
				responsive: false
			}
		} );

		charts.set( canvas, chart );

	}


	function generateFakeData( count ) {
		return Array.from( { length: count }, () =>
			Math.floor( Math.random() * 200 ) + 50
		);
	}

	function createGraphCard( title ) {
		const fs = document.createElement( 'fieldset' );
		fs.className = 'analytics-graph';

		const legend = document.createElement( 'legend' );
		legend.textContent = title;

		const canvas = document.createElement( 'canvas' );
		canvas.style.height = '260px';

		fs.appendChild( legend );
		fs.appendChild( canvas );

		return { fieldset: fs, canvas };
	}


	applyButton.addEventListener( 'click', () => {
		// Fake labels for now
		const labels = [
			'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
			'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
		];

		let compareData = null;
		if ( compareCheckbox.checked ) {
			compareData = generateFakeData( labels.length );
		}

		graphs.forEach( graph => {
			const data = generateFakeData( labels.length );
			renderChart( graph.canvas, labels, data, compareData );
		} );
	} );

	// Avengers Assemble!
	analyticsForm.appendChild( fieldset );
	const heading = document.getElementById( 'firstHeading' );
	const content = document.getElementById( 'mw-content-text' );

	if ( heading && heading.parentElement ) {
			heading.parentElement.after( analyticsForm );
		} else {
			content.prepend( analyticsForm );
	}


	// Display the date picker if custom is selected, otherwise hide
	rangeSelect.addEventListener( 'change', () => {
		if ( rangeSelect.value === 'custom' ) {
			dateFieldset.style.display = '';
		} else {
			dateFieldset.style.display = 'none';
		}
	} );

	let startPicker;
	let endPicker;

	if ( typeof flatpickr !== 'undefined' ) {
		startPicker = flatpickr( '#analytics-start-date', {
			dateFormat: 'Y-m-d',
			onChange: ( selectedDates ) => {
				if ( selectedDates.length ) {
					endPicker.set( 'minDate', selectedDates[0] );
					validateForm();
				}
			}
		} );

		endPicker = flatpickr( '#analytics-end-date', {
			dateFormat: 'Y-m-d',
			onChange: ( selectedDates ) => {
				if ( selectedDates.length ) {
					startPicker.set( 'maxDate', selectedDates[0] );
					validateForm();
				}
			}
		} );
	}

	// Validate form so users can select the apply button unless the dates are valid
	function validateForm() {
	if ( rangeSelect.value !== 'custom' ) {
		applyButton.disabled = false;
		applyButton.classList.remove( 'is-disabled' );
		return;
	}

	const start = startDate.value;
	const end = endDate.value;

	const isValid = start && end && start <= end;

	applyButton.disabled = !isValid;
	applyButton.classList.toggle( 'is-disabled', !isValid );

	rangeSelect.addEventListener( 'change', () => {
		dateFieldset.style.display =
			( rangeSelect.value === 'custom' ) ? '' : 'none';

		validateForm();
	} );
}



//         if ( typeof flatpickr !== 'undefined' ) {
//     flatpickr( '#analytics-start-date', {
//         dateFormat: 'Y-m-d'
//     } );

//     flatpickr( '#analytics-end-date', {
//         dateFormat: 'Y-m-d'
//     } );
// }


} );