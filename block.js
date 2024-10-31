( function ( blocks, React, data, blockEditor ) {
    var el = React.createElement,
			registerBlockType = blocks.registerBlockType,
			useSelect = data.useSelect;

    blocks.registerBlockType( 'muzodo/events', {
        edit: function (props) {
			const blockProps = blockEditor.useBlockProps();
			
			/*
			const greenBackground = {
                backgroundColor: '#090',
                color: '#fff',
                padding: '20px',
            };
            const blockProps = blockEditor.useBlockProps( {
                style: greenBackground,
            } );
			*/
			
			function updateApiKey(value) {
				props.setAttributes({apiKey: value})
			}

			function updateFilterPrivate(checked) {
				props.setAttributes({filterPrivate: checked})
			}

			function updateFilterCancelled(checked) {
				props.setAttributes({filterCancelled: checked})
			}

			function updateFilterUnconfirmed(checked) {
				props.setAttributes({filterUnconfirmed: checked})
			}

			function updateDateTimeSeparate(checked) {
				props.setAttributes({dateTimeSeparate: checked})
			}

			function updateDateFormat(value) {
				props.setAttributes({dateFormat: value})
			}

			function updateTimeFormat(value) {
				props.setAttributes({timeFormat: value})
			}

			function updateShowVenue(checked) {
				props.setAttributes({showVenue: checked})
			}

			function updateNoEventsText(text) {
				props.setAttributes({noEventsText: text})
			}


			var content = el(
				"div",
				null,
				el("h4", null, "Muzodo"),

				el(wp.components.TextControl,
				{
					label: 'Muzodo API Key',
					value: props.attributes.apiKey,
					onChange: updateApiKey,
				}),

				el(wp.components.CheckboxControl,
				{
					label: 'Filter private events',
					checked: props.attributes.filterPrivate == 1,
					onChange: updateFilterPrivate,
				}),

				el(wp.components.CheckboxControl,
				{
					label: 'Filter cancelled events',
					checked: props.attributes.filterCancelled == 1,
					onChange: updateFilterPrivate,
				}),

				el(wp.components.CheckboxControl,
				{
					label: 'Filter unconfirmed events',
					checked: props.attributes.filterUnconfirmed == 1,
					onChange: updateFilterUnconfirmed,
				}),

				el(wp.components.CheckboxControl,
				{
					label: 'Date and Time in separate columns',
					checked: props.attributes.dateTimeSeparate == 1,
					onChange: updateDateTimeSeparate,
				}),

				el(wp.components.SelectControl,
				{
					label: "Date format",
					options: [
						{
							label: "Full with day of week (day dd-mon-year)",
							value: 'LONG-DAY'
						},
						{
							label: "Full (dd mon year)",
							value: 'LONG'
						},
						{
							label: "Short (dd mon)",
							value: 'SHORT-DD-MON'
						},
						{
							label: "Short (mon dd)",
							value: 'SHORT-MON-DD'
						},
					],
					value: props.attributes.dateFormat,
					onChange: updateDateFormat,
				}),
				
				el(wp.components.SelectControl,
				{
					label: "Time format",
					options: [
						{
							label: "Start and End Time",
							value: 'START-AND-END'
						},
						{
							label: "Start Time only",
							value: 'START-ONLY'
						},
						{
							label: "Do not display time",
							value: 'NONE'
						},
					],
					value: props.attributes.timeFormat,
					onChange: updateTimeFormat,
				}),

				el(wp.components.CheckboxControl,
				{
					label: 'Show Venue (public events only)',
					checked: props.attributes.showVenue == 1,
					onChange: updateShowVenue,
				}),
				
				el(wp.components.TextControl,
				{
					label: 'No events message',
					placeholder: 'e.g. There are no upcoming events',
					value: props.attributes.noEventsText,
					onChange: updateNoEventsText,
				}),
				
			);
			
			return el( 'div', blockProps, content ); 
			//return el( 'div', null, content ); 
        },
        save: function (props) {
            return el( 'p', {}, 'Hola muz (from the frontend).' );
        },
    } );
} )( 
	window.wp.blocks, 
	window.React, 
	window.wp.data, 
	window.wp.blockEditor
);
