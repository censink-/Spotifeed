$(init);

var firstentry = false,
	showinfo = true;

function init()
{
	if ( $('#welcome-btn').length )
	{
		setTimeout(function () {
			$('#box').addClass('big');
			$('#login-msg').addClass('hidden');
		}, 500);
		setTimeout(function () {
			$('#steps').removeClass('hidden');
		}, 1000);
		setTimeout(function () {
			//demo();
			getFromApi(2, "exist");
		}, 1200);
	}
}

function demo()
{
	setTimeout(function () {
		setStatus(2, 'completed');
		setStatus(3, 'active');
	}, 1000);
	setTimeout(function () {
		setStatus(3, 'completed');
		setStatus(4, 'active');
	}, 4000);
	setTimeout(function () {
		setStatus(4, 'failed');
	}, 4500);
}

function setStatus( step, status, result)
{
	var el = $('#step-' + step);

	el.removeClass('pending active completed failed');
	el.addClass(status);

	if (result != null && showinfo)
	{
		el.find(".step-result").text(result);
		el.addClass("has-result");
	}
}

function getFromApi( step, action, result)
{
	$.getJSON("api.php?action=" + action, gotFromApi);

	setStatus(step, "active", result);
}

function gotFromApi( data )
{
	console.log("Got from API, step " + data.step + ": '" + data.action + "':");
	console.log(data);

	if ( data.code == 200 || data.code == 201 || data.code == 204 )
	{
		setStatus(data.step, "completed");
	}
	else
	{
		setStatus(data.step, "failed");
	}

	switch (data.action)
	{
		case "exist":
			console.log("Existing");
			if ( data.code == 200 || data.code == 201 )
			{
				if ( data.code == 201 )
				{
					firstentry = true;
					setStatus(data.step, "completed", "Nope, so we've made it for you");
				}
				else
				{
					setStatus(data.step, "completed", "Yes, we'll be updating it now");
				}

				getFromApi(3, "following");
			}
			else
			{
				setStatus(2, "failed");
			}
			break;
		case "following":
			console.log("Following");
			if ( data.count == null || data.count == 0)
			{
				setStatus(3, "failed");
			} else
			{
				console.log("Found " + data.count + " artists:");
				console.log(data.artists);

				setStatus(3, "completed", "You're currently following " + data.count + " artists");

				getFromApi(4, "tracks", "This might take a while..");
			}
			break;
		case "tracks":
			var artistnum = Number(data.artistnum),
				artistsum = data.artistsum,
				albumsum = data.albumsum,
				tracksum = data.tracks.length;

			if (artistnum >= artistsum)
			{
				setStatus(4, "completed", "Got " + tracksum + " tracks from " + albumsum + " albums");

				getFromApi(5, "update");
			}
			else
			{
				setStatus(4, "active", "Got tracks from " + artistnum + "/" + artistsum + " artists");

				getFromApi(4, "tracks&artist=" + (artistnum + 1));
			}
			break;
		case "update":

			var songnum = data.songnum,
				songsum = data.songsum,
				part = Number(data.part);

			setStatus(5, "active", "Added " + songnum + " of " + songsum + " new tracks");

			if (songnum >= songsum)
			{
				setStatus(5, "completed", "Added " + songsum + " new tracks");

				getFromApi(6, "info");
			}
			else
			{
				getFromApi(5, "update&part=" + (part + 1));
			}
			break;
		case "info":
			if (data.code == 204)
			{
				setStatus(6, "completed", "Done! Feel free to modify the playlist now");
			}
			break;
	}
}