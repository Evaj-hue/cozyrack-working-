#include <Arduino.h>
#include "HX711.h"
#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266HTTPClient.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <Pushbutton.h>

// HX711 circuit wiring
const int LOADCELL_DOUT_PIN = 12;
const int LOADCELL_SCK_PIN = 13;

HX711 scale;
float reading;
float lastReading = 0.0;

// Calibration factor and threshold
#define CALIBRATION_FACTOR 116.36
const float weightThreshold = 0.05; // Ignore weights below 50 grams
const float itemWeight = 0.30; // Each item's weight in kilograms (300g)

// OLED Display
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET -1
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

const char* ssid = "BUILDING";
const char* password = "bolbolings";

// Initialize the HTTPClient and WiFiClient
HTTPClient http;
WiFiClient client;

// Button
#define BUTTON_PIN 14
Pushbutton button(BUTTON_PIN);

void displayWeightAndCount(float weight, int count) {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(WHITE);

  // Display weight
  display.setCursor(0, 10);
  display.println("Weight:");
  display.setCursor(0, 30);
  display.setTextSize(2);
  display.print(weight, 2); // Show weight with 2 decimal places
  display.print(" kg");

  // Display item count
  display.setTextSize(1);
  display.setCursor(0, 50);
  display.print("Count: ");
  display.setTextSize(2);
  display.setCursor(60, 45);
  display.print(count);

  display.display();
}

void setup() {
  Serial.begin(115200);

  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println(F("SSD1306 allocation failed"));
    for (;;);
  }
  delay(2000);
  display.clearDisplay();
  display.setTextColor(WHITE);

  Serial.println("Initializing the scale");
  scale.begin(LOADCELL_DOUT_PIN, LOADCELL_SCK_PIN);

  scale.set_scale(CALIBRATION_FACTOR); // Set calibrated value
  scale.tare(); // Reset scale to 0

  Serial.println("Taring... Wait a moment.");
  delay(2000); // Allow tare operation to stabilize
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  if (button.getSingleDebouncedPress()) {
    Serial.print("Tare...");
    scale.tare(); // Reset scale
  }

  if (scale.wait_ready_timeout(200)) {
    reading = scale.get_units();
    float weightInKg = reading / 1000.0; // Convert grams to kilograms

    // Apply threshold for noise
    if (weightInKg < weightThreshold) {
      weightInKg = 0.0; // Treat values below threshold as zero
    }

    // Calculate number of items
    int itemCount = round(weightInKg / itemWeight);

    // Print to Serial Monitor
    Serial.print("Weight: ");
    Serial.print(weightInKg, 2); // Display weight in kg with 2 decimal places
    Serial.println(" kg");

    Serial.print("Item Count: ");
    Serial.println(itemCount);

    // Update OLED only if the reading changes
    if (weightInKg != lastReading) {
      displayWeightAndCount(weightInKg, itemCount);
      lastReading = weightInKg;
    }
  } else {
    Serial.println("HX711 not found.");
  }
  
  float weight = scale.get_units();  // Fixed here by changing loadCell to scale
  
  // Prepare the URL for HTTP request
  String host = "http://192.168.68.204/weight/putdata.php?weight=" + String(weight) + "&status=1";
  
  // Check Wi-Fi status
  if (WiFi.status() == WL_CONNECTED) {
    // Send HTTP request to server
    http.begin(client, host);  // Start the HTTP request
    int httpResponseCode = http.GET();  // Send GET request
    delay(200);
    
    // Check for successful HTTP response
    if (httpResponseCode > 0) {
      Serial.println("HTTP Response code: " + String(httpResponseCode));
      String response = http.getString();
      Serial.println("Response body: " + response);
    } else {
      Serial.println("Error on HTTP request: " + String(httpResponseCode));
    }

    // Close the HTTP connection
    http.end();
  } else {
    Serial.println("WiFi not connected");
  }
  
  // Wait for 1 second before reading weight again
  delay(1000);
}
