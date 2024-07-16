return new google.maps.StyledMapType(
	[
	{
		"featureType": "administrative",
		"elementType": "labels.text.fill",
		"stylers": [
		{
			"color": "#000000"
		},
		{
			"visibility": "on"
		},
		{
			"weight": "0.01"
		}
		]
	},
	{
		"featureType": "administrative",
		"elementType": "labels.text.stroke",
		"stylers": [
		{
			"visibility": "off"
		}
		]
	},
	{
		"featureType": "landscape",
		"elementType": "all",
		"stylers": [
		{
			"visibility": "on"
		},
		{
			"color": "#c0c0c0"
		}
		]
	},
	{
		"featureType": "poi",
		"elementType": "all",
		"stylers": [
		{
			"visibility": "off"
		}
		]
	},
	{
		"featureType": "road",
		"elementType": "all",
		"stylers": [
		{
			"saturation": -100
		},
		{
			"lightness": "-11"
		},
		{
			"color": "#9b9b9b"
		}
		]
	},
	{
		"featureType": "road",
		"elementType": "geometry.stroke",
		"stylers": [
		{
			"weight": "0.35"
		},
		{
			"visibility": "on"
		},
		{
			"color": "#9b9b9b"
		}
		]
	},
	{
		"featureType": "road",
		"elementType": "labels.text",
		"stylers": [
		{
			"visibility": "simplified"
		},
		{
			"color": "#6f6f6f"
		}
		]
	},
	{
		"featureType": "road.highway",
		"elementType": "all",
		"stylers": [
		{
			"visibility": "simplified"
		},
		{
			"lightness": "-7"
		},
		{
			"color": "#9b9b9b"
		}
		]
	},
	{
		"featureType": "road.arterial",
		"elementType": "labels.icon",
		"stylers": [
		{
			"visibility": "off"
		}
		]
	},
	{
		"featureType": "transit",
		"elementType": "all",
		"stylers": [
		{
			"visibility": "off"
		},
		{
			"lightness": "-5"
		},
		{
			"color": "#ffffff"
		}
		]
	},
	{
		"featureType": "water",
		"elementType": "all",
		"stylers": [
		{
			"color": "#ffffff"
		},
		{
			"visibility": "on"
		}
		]
	}
	]
	)