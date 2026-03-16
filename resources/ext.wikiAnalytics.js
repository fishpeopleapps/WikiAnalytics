mw.loader.using( 'ext.wikiAnalytics' ).then( () => {

	const charts = new Map();
	const api = new mw.Api();
	let namespaceChart = null;
	const METRICS = [
	{
		key: 'edit_count',
		label: 'Edits'
	},
	{
		key: 'article_count',
		label: 'Articles'
	},
	{
		key: 'user_count',
		label: 'Total Users'
	},
	{
		key: 'active_user_count',
		label: 'Active Users'
	},
	{
		key: 'file_count',
		label: 'Files'
	},
	{
		key: 'page_count',
		label: 'Pages'
	},
	{
		key: 'category_count',
		label: 'Categories'
	},
	{
		key: 'template_count',
		label: 'Templates'
	},
	{
		key: 'page_views',
		label: 'Page Views'
	},
	{
		key: 'word_count',
		label: 'Word Count'
	},
	{
		key: 'pages_created',
		label: 'Pages Created'
	},
	{
		key: 'edits_this_month',
		label: 'Edits This Month'
	},
	{
		key: 'uploads_this_month',
		label: 'Uploads This Month'
	},
	{
		key: 'upload_bytes_this_month',
		label: 'Upload Bytes This Month'
	},
	{
		key: 'new_users',
		label: 'New Users'
	},
	{
		key: 'returning_users',
		label: 'Returning Users'
	},
	{
		key: 'active_editors',
		label: 'Active Editors'
	}
];



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

// ---- Graphs
	const resultsHeading = document.createElement( 'h3' );
	resultsHeading.textContent = 'Results';
	fieldset.appendChild( resultsHeading );

	const graphGrid = document.createElement( 'div' );
	graphGrid.className = 'analytics-grid';
	fieldset.appendChild( graphGrid );

	const graphs = METRICS.map( metric => {
	const graph = createGraphCard( metric.label );
	graphGrid.appendChild( graph.fieldset );
	return {
			...graph,
			metricKey: metric.key
		};
	} );

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
				responsive: false,
				plugins: {
					legend: {
						display: false
					}
				}
			}
		} );

		charts.set( canvas, chart );

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
		const params = {
			action: 'wikianalytics',
			format: 'json'
		};

		// Map UI range → API scope
		switch ( rangeSelect.value ) {
			case 'thisMonth':
				params.scope = 'current';
				break;
			case 'thisYear':
				params.scope = 'year';
				params.year = new Date().getFullYear();
				break;
			case 'custom':
				params.scope = 'range';
				params.startYear = parseInt( startDate.value.slice( 0, 4 ), 10 );
				params.startMonth = parseInt( startDate.value.slice( 5, 7 ), 10 );
				params.endYear = parseInt( endDate.value.slice( 0, 4 ), 10 );
				params.endMonth = parseInt( endDate.value.slice( 5, 7 ), 10 );
				break;
			default:
				params.scope = 'last12';
		}

		api.get( params ).then( response => {
			const data = response.wikianalytics;

			const labels = data.months.map(
				m => `${ m.year }-${ String( m.month ).padStart( 2, '0' ) }`
			);

			graphs.forEach( graph => {
				const values = data.months.map(
					m => m[ graph.metricKey ] ?? 0
				);

				renderChart(
					graph.canvas,
					labels,
					values
				);
			} ); // ends graphs.forEach

			// for namespace display
			const namespaceData = data.namespaces.filter( row => {
				return data.months.some(
					m => m.year === row.year && m.month === row.month
				);
			} );

			if ( namespaceData && namespaceData.length ) {

				const namespaceMap = new Map();

				namespaceData.forEach( row => {
					const key = row.namespace_id;

					if ( !namespaceMap.has( key ) ) {
						if ( !namespaceMap.has( key ) ) {
							namespaceMap.set( key, {
								namespace_id: key,
								page_count: 0,
								edit_count: 0
							} );
						}

						const ns = namespaceMap.get( key );
						ns.page_count += row.page_count;
						ns.edit_count += row.edit_count;
					}
				} );

				const labels = [];
				const pageCounts = [];
				const editCounts = [];

				const sortedNamespaces = Array.from( namespaceMap.values() )
				// 6 is file pages which I don't think should be included here
					.filter( ns =>
						ns.namespace_id !== 6 &&
						( ns.page_count > 0 || ns.edit_count > 0 )
					)
					.sort( ( a, b ) => b.page_count - a.page_count );

				sortedNamespaces.slice( 0, 10 ).forEach( ns => {
					// use the human name vs the number code
					// this was showing correct names for everything except main which was showing as 0
					// labels.push( mw.config.get( 'wgFormattedNamespaces' )[ ns.namespace_id ] || `NS ${ns.namespace_id}` );
					const nsName = mw.config.get( 'wgFormattedNamespaces' )[ ns.namespace_id ];
					labels.push( nsName || 'Main' );
					pageCounts.push( ns.page_count );
					editCounts.push( ns.edit_count );
				} );

				const namespaceGraph = createGraphCard( 'Namespace Breakdown' );
				graphGrid.appendChild( namespaceGraph.fieldset );

				const namespaceCanvas = namespaceGraph.canvas;

				if ( namespaceChart ) {
					namespaceChart.destroy();
				}

				namespaceChart = new Chart( namespaceCanvas.getContext( '2d' ), {
					type: 'bar',
					data: {
						labels,
						datasets: [
							{
								label: 'Pages',
								data: pageCounts,
								backgroundColor: '#4e79a7'
							},
							{
								label: 'Edits',
								data: editCounts,
								backgroundColor: '#8ecae6'
							}
						]
					},
					options: {
						indexAxis: 'y',
						responsive: true,
						maintainAspectRatio: false,
						plugins: {
							legend: {
								display: true
							}
						}
					}
				} );

			}

		} );
	} );

	applyButton.click();


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

} );