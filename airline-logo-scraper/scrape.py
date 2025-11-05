from urllib.request import urlretrieve
import json

with open("airlines.json", "r") as file:
    availableAirlines = json.load(file)

for airline in availableAirlines:
    url = ("https://www.avcodes.co.uk/images/logos/" + airline + ".png")
    filename = "logos/" + airline + ".png"
    try:        
        path, headers = urlretrieve(url, filename)
    except:
        print("Generic error downloading airline: " + airline)
    else:
        print("Downloaded airline: " + airline)

    print("=====")