# Leyline

Leyline is a system for storing and viewing your personal location history. It uses the Android app '[Backitude](https://play.google.com/store/apps/details?id=gaugler.backitude)' for getting your location, so you'll need an Android phone.

![Screenshot](http://i.imgur.com/wAPjnqc.png)

## About

I started coding this as a response to discovering my location was being tracked by Google Location History. It creates a MySQL database of your locations, and also has a viewer which shows your movements, 'hotspots', and favourite locations.

Leyline is a very long way from being finished. You can find me at [@tarasyoung](http://twitter.com/tarasyoung).

## Setup

*Ingredients*

* An Android phone
* The free Android app 'Backitude'
* Access to a MySQL database
* Access to some web space with PHP (including rights to change file permissions)
* Leyline
* 5 minutes to install it (hopefully)

*Method*

1. Upload the files to your server, make the folder world writeable (777) and point your browser towards install.php.

2. Follow the instructions until Leyline is installed.

3. Change the folder permissions back to 755.

4. Set up Backitude on your phone (see below) so it starts reporting your location.

5. After a few hours of checkins, visit the URL in your browser to see all locations recorded.

*Setting up Backitude*

1. Install Backitude on your phone from the Play Store.
2. Set up Backitude as follows, checking (or ticking, if you're British) the relevant boxes:

- Enable Backitude [checked - do this last!]
	- Extras - no change
	- Settings
		- Standard mode settings
			- Time interval selection = 5 minutes
			- Location polling timeout = 30 seconds
			- Display update message [checked]
		- Docked mode settings
			- Docked mode enabled [unchecked]
		- Wi-Fi mode settings
			- Wi-Fi mode enabled [checked]
			- Time interval options = 30 minutes
			- Location polling timeout = 30 seconds
		- Update settings
			- Location steals [checked]
			- Maximum steals rate = 1 minute
			- Minimum change in distance = 50 metres
			- Minimum interval = 30 minutes
			- Data roam/reduction mode [unchecked]
		- Accuracy settings
			- Minimum GPS accuracy = 12 metres
			- Minimum Wi-Fi accuracy = 100 metres
		- Custom server settings
			- Server URL = http://yourdomain/set.php
			- Request type = POST
			- Successful repsonse codes = 201,200
			- Offline storage settings
				- Offline storage enabled [checked]
				- Sync options = Any data signal available
				- Display sync message [checked]
			- Authentication options = Basic auth only (if you choose to put auth on your folder)
			- User Name = whatever (if you choose to put auth on your folder)
			- Password = whatever (if you choose to put auth on your folder)
			- Custom field 1 = enter your Leyline username here
			- Custom field 2 = enter your Leyline password here
			- *Request parameter keys*
			- Latitude = latitude
			- Longitude = longitude
			- Accuracy = accuracy
			- Speed = (No Value - Do Not POST)
			- Altitude = (No Value - Do Not POST)
			- Direction bearing = (No Value - Do Not POST)
			- Location timestamp (UTC) = loc_timestamp
			- Request timestamp (UTC) = req_timestamp
			- Timezone offset = offset
			- Custom field 1 = u
			- Custom field 2 = p
		- Push update settings
			- [all unchecked/blank]
		- Internal memory storage option = Do not store locally
		- Google account = No current account is saved
		- Status Bar Icon = Show a status bar icon
		- Display Failure Notification [checked]
	- Advanced settings
		- Provider priority = GPS, with Wi-Fi/Tower triang backup
		- Time zone offset [unchecked]
		- Wake lock enabled [checked]
		- Wi-Fi lock enabled [unchecked]

That's it!