import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";

class Flight {
    constructor(obj) {
        Object.assign(this, obj);
    }
}

const API_URL = "https://corsproxy.io/?https://booking.it.ivao.aero/api/flights";
//const WAZZUP_URL = "https://corsproxy.io/?https://cdn.it.ivao.aero/whazzup.json";
const WAZZUP_URL = "https://corsproxy.io/?https://api.ivao.aero/v2/tracker/whazzup"; 
const API_KEY = "2024IoSonoCarmineAirSiciliaColBidplano25yearsOfIvaoIt";
// For testing: set these to a static window, or leave as null for dynamic
const STATIC_FILTER_START = null; // "2025-11-08 12:00";


function getUTCString(date) {
    // Returns YYYY-MM-DD HH:mm (UTC)
    const y = date.getUTCFullYear();
    const m = String(date.getUTCMonth() + 1).padStart(2, '0');
    const d = String(date.getUTCDate()).padStart(2, '0');
    const h = String(date.getUTCHours()).padStart(2, '0');
    const min = String(date.getUTCMinutes()).padStart(2, '0');
    return `${y}-${m}-${d} ${h}:${min}`;
}

function isBetween(dateStr, start, end) {
    const d = new Date(dateStr.replace(" ", "T"));
    return d >= new Date(start + ":00") && d <= new Date(end + ":00");
}




export default function Board() {
    const [flights, setFlights] = useState([]);
    const [currentDate, setCurrentDate] = useState("");
    const [currentTime, setCurrentTime] = useState("");
    const [loadingFlights, setLoadingFlights] = useState(false);
    const [loadingPilots, setLoadingPilots] = useState(false);
    const loading = loadingFlights || loadingPilots;
    const { type } = useParams();
    const typeParam = type || "departure";
    const isDepartures = typeParam === "departure";
    const updateEveryMs = 60000; // 1 minute


    useEffect(() => {
        function fetchFlights() {
            // Calculate filter window: static for testing, else dynamic (now to now+2h UTC)
            let filterStart, filterEnd, now;
            if (STATIC_FILTER_START) {
                filterStart = STATIC_FILTER_START;
                now = new Date(STATIC_FILTER_START); // still use now for "30 min in the past" logic
            } else {
                now = new Date();
            }
            filterStart = getUTCString(now);
            const end = new Date(now.getTime() + 2 * 60 * 60 * 1000);
            filterEnd = getUTCString(end);

            setLoadingFlights(true);
            fetch(API_URL, {
                headers: {
                    "X-Api-Key": API_KEY,
                },
            })
                .then((res) => res.json())
                .then((data) => {
                    const filtered = data
                        .map((f) => new Flight(f))
                        .filter((f) => {
                            if (f.type_of_flight !== typeParam) return false;
                            // eobt in window
                            if (!isBetween(f.eobt, filterStart, filterEnd)) return false;
                            // eobt not more than 30 min in the past
                            const eobtDate = new Date(f.eobt.replace(" ", "T"));
                            if (eobtDate.getTime() < now.getTime() - 30 * 60 * 1000) return false;
                            return true;
                        });
                    setFlights(filtered);
                })
                .finally(() => setLoadingFlights(false));
        }
        fetchFlights();
        const interval = setInterval(fetchFlights, updateEveryMs);
        return () => clearInterval(interval);
    }, [typeParam]);


    // Fetch and merge live pilot data from IVAO Whazzup every 1 minute
    useEffect(() => {
        function fetchPilots() {
            if (!flights.length) return;
            setLoadingPilots(true);
            fetch(WAZZUP_URL)
                .then((res) => res.json())
                .then((data) => {
                    if (!data.clients || !data.clients.pilots) return;

                    // Build a map for quick lookup by callsign
                    const pilotMap = new Map();
                    data.clients.pilots.forEach((pilot) => {
                        if (pilot.callsign) {
                            pilotMap.set(pilot.callsign.toUpperCase(), pilot);
                        }
                    });

                    // Merge pilot data into flights
                    setFlights((prevFlights) =>
                        prevFlights.map((flight) => {
                            const pilot = pilotMap.get(flight.callsign.toUpperCase());
                            if (pilot && pilot.lastTrack) {
                                return {
                                    ...flight,
                                    groundSpeed: pilot.lastTrack.groundSpeed,
                                    state: pilot.lastTrack.state,
                                    onGround: pilot.lastTrack.onGround,
                                };
                            }
                            return flight;
                        })
                    );
                })
                .catch((error) => {
                    console.error("Error fetching pilot data:", error);
                })
                .finally(() => setLoadingPilots(false));
        }
        fetchPilots();
        const interval = setInterval(fetchPilots, updateEveryMs);
        return () => clearInterval(interval);
    }, [flights.length]);

    useEffect(() => {
        function updateDateTime() {
            const now = new Date();
            // Date: day number and month name
            const day = now.getUTCDate();
            const month = now.toLocaleString("en-US", { month: "long", timeZone: "UTC" });
            setCurrentDate(`${day} ${month}`);
            // Time: UTC HH:mm
            const hours = String(now.getUTCHours()).padStart(2, '0');
            const minutes = String(now.getUTCMinutes()).padStart(2, '0');
            setCurrentTime(`${hours}:${minutes}`);
        }
        updateDateTime();
        const interval = setInterval(updateDateTime, 1000);
        return () => clearInterval(interval);
    }, []);

    function Description(item) {
        if(isDepartures) return `${item.flight.destination_city} (${item.flight.destination_iata})`;
        return `${item.flight.origin_city} (${item.flight.origin_iata})`;
    }
    
    function getRandomInt(max) {
        return Math.floor(Math.random() * max)+1;
    }

    function State(item){
        if(item.flight.state === "Boarding") return ' Boarding';
        if(item.flight.state === "Departing") return ' Departed 🛫';
        if(item.flight.state === "Approach") return ' Approaching 🛬';
        if(item.flight.state === "Landed" ) return ' Landed';
        if(item.flight.state === "On Blocks") return ' Arrived - 🛄 Baggage claim nr. ' + getRandomInt(15);
    }

    return (
        <div className="departure-board">
            {loading && (
                <div className="loader-overlay">
                    <div className="loader-spinner"></div>
                    <div className="loader-text">Loading flight data...</div>
                </div>
            )}
            <div className="board-header">
                <div>
                    <div className="board-date">{currentDate}</div>
                    <div className="board-title">
                        <span className="board-icon">✈</span>
                        <span>{isDepartures ? "Let's Go! - Departures" : "Welcome! - Arrivals"}</span>
                    </div>
                </div>
                <div className="board-time" id="current-time">{currentTime}</div>
            </div>

            <div className="column-headers">
                <div>Time</div>
                <div>{isDepartures ? "Destination" : "Origin"} </div>
                <div>Flight Number</div>
                <div>Gate</div>
                <div>State</div>
            </div>

            <div className="flights-container">
                {flights.map((flight, idx) => (
                    <div className="flight-row" key={flight.callsign + idx}>
                        <div className="flight-time">{flight.eobt.slice(11, 16)}</div>
                        <div className="flight-destination"><Description flight={flight} /></div>
                        <div>
                            <div className="airline-logo">
                                <img src={`https://cdn.it.ivao.aero/airlines/${flight.callsign.substring(0, 3)}.png`} alt="Airline" className="airline-logo" />
                            </div>
                            <div className="flight-number">
                                {flight.callsign}
                            </div>
                        </div>
                        <div className="gate-badge">{flight.gate}</div>
                        <div className="status-text">
                            {/* <>
                                {typeof flight.onGround === 'boolean' && (flight.onGround ? ' Boarding' : 'Departed 🛫 ')}
                            </> */}
                            <State flight={flight} />
                        </div>
                    </div>
                ))}
            </div>
            
            <div className="footer-qr">
                <div className="qr-placeholder">
                    <img src="data:image/png;base64,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" alt="QR Code" className="qr-code" />
                </div>
                <div>
                    <div className="qr-text">Scan the QR Code!</div>
                    <div className="qr-subtext">Text us to get all your flight updates and the informations via Whatsapp!</div>
                </div>
            </div>
        </div>
    );
}