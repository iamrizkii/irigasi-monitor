/*
 * =============================================================================
 * KODE ESP32 UNTUK SISTEM IRIGASI IOT - GESTI
 * =============================================================================
 * 
 * Kode ini merupakan versi yang sudah ditambahkan koneksi WiFi dan HTTP
 * untuk berkomunikasi dengan website Laravel.
 * 
 * CARA PAKAI:
 * 1. Ganti WIFI_SSID dan WIFI_PASSWORD dengan WiFi yang akan digunakan
 * 2. Ganti SERVER_URL dengan URL website Laravel (contoh: http://192.168.1.100/gesti)
 * 3. Upload ke ESP32
 * 
 * LIBRARY YANG DIBUTUHKAN:
 * - ESP32Servo
 * - LiquidCrystal_I2C
 * - WiFi (sudah termasuk di ESP32)
 * - HTTPClient (sudah termasuk di ESP32)
 * - ArduinoJson (install dari Library Manager)
 * 
 * =============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <ESP32Servo.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// ================= KONFIGURASI WIFI =================
// GANTI DENGAN WIFI ANDA
const char* WIFI_SSID = "NAMA_WIFI_ANDA";
const char* WIFI_PASSWORD = "PASSWORD_WIFI_ANDA";

// ================= KONFIGURASI SERVER =================
// GANTI DENGAN URL LARAVEL ANDA
// Contoh localhost: "http://192.168.1.100/gesti"
// Contoh online: "http://gesti.example.com"
const char* SERVER_URL = "http://192.168.1.100/gesti";

// Interval pengiriman data (dalam milidetik)
const unsigned long SEND_INTERVAL = 5000; // 5 detik

// ================= LCD =================
LiquidCrystal_I2C lcd(0x27, 20, 4);

// ================= PIN SENSOR TANAH =================
#define SOIL1 34
#define SOIL2 35
#define SOIL3 32
#define SOIL4 33

// ================= KALIBRASI TANAH (ADC) =================
#define DRY1 4095
#define WET1 1328
#define DRY2 4095
#define WET2 1217
#define DRY3 4095
#define WET3 1100
#define DRY4 4095
#define WET4 1104

// ================= ULTRASONIK =================
#define TRIG_MAIN 18
#define ECHO_MAIN 5

#define TRIG_MID 4
#define ECHO_MID 2

#define TRIG_TANK 16
#define ECHO_TANK 17

// ================= SERVO =================
#define SERVO_MAIN 15
#define SERVO_1 23
#define SERVO_2 25
#define SERVO_3 26
#define SERVO_4 27

// ================= RELAY POMPA =================
#define RELAY_PUMP 14 // ACTIVE LOW

// ================= OBJEK SERVO =================
Servo servoMain, servo1, servo2, servo3, servo4;

// ================= VARIABEL GLOBAL =================
unsigned long lastSendTime = 0;
String currentMode = "auto"; // "auto" atau "manual"

// Variabel untuk menyimpan perintah dari web (mode manual)
bool webPumpCommand = false;
int webGateMainCommand = 0;
int webGate1Command = 0;
int webGate2Command = 0;
int webGate3Command = 0;
int webGate4Command = 0;

// Data sensor saat ini
int m1, m2, m3, m4;
float airMain, airMid, airTank;
bool pumpStatus = false;
int gateMain = 0, gate1 = 0, gate2 = 0, gate3 = 0, gate4 = 0;
String systemStatus = "Stabil";

// ================= FUNGSI KONEKSI WIFI =================
void connectWiFi() {
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(WIFI_SSID);
  
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi...");
  lcd.setCursor(0, 1);
  lcd.print(WIFI_SSID);
  
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    lcd.setCursor(attempts % 20, 2);
    lcd.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi terhubung!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected!");
    lcd.setCursor(0, 1);
    lcd.print("IP: ");
    lcd.print(WiFi.localIP());
    delay(2000);
  } else {
    Serial.println("\nGagal terhubung WiFi!");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi GAGAL!");
    lcd.setCursor(0, 1);
    lcd.print("Mode Offline...");
    delay(2000);
  }
}

// ================= FUNGSI KIRIM DATA KE SERVER =================
void sendDataToServer() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi tidak terhubung, skip pengiriman data");
    return;
  }
  
  HTTPClient http;
  String url = String(SERVER_URL) + "/api/sensor-data";
  
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  
  // Buat JSON payload
  StaticJsonDocument<512> doc;
  doc["petak1_moisture"] = m1;
  doc["petak2_moisture"] = m2;
  doc["petak3_moisture"] = m3;
  doc["petak4_moisture"] = m4;
  doc["water_main"] = airMain < 900 ? airMain : -1;
  doc["water_mid"] = airMid < 900 ? airMid : -1;
  doc["water_tank"] = airTank < 900 ? airTank : -1;
  doc["pump_status"] = pumpStatus;
  doc["gate_main"] = gateMain;
  doc["gate1"] = gate1;
  doc["gate2"] = gate2;
  doc["gate3"] = gate3;
  doc["gate4"] = gate4;
  doc["system_status"] = systemStatus;
  
  String jsonPayload;
  serializeJson(doc, jsonPayload);
  
  Serial.println("Mengirim data ke server...");
  Serial.println(jsonPayload);
  
  int httpCode = http.POST(jsonPayload);
  
  if (httpCode > 0) {
    Serial.printf("HTTP Response code: %d\n", httpCode);
    String response = http.getString();
    Serial.println("Response: " + response);
  } else {
    Serial.printf("HTTP Error: %s\n", http.errorToString(httpCode).c_str());
  }
  
  http.end();
}

// ================= FUNGSI AMBIL PERINTAH DARI SERVER =================
void getCommandsFromServer() {
  if (WiFi.status() != WL_CONNECTED) {
    return;
  }
  
  HTTPClient http;
  String url = String(SERVER_URL) + "/api/device-status";
  
  http.begin(url);
  http.addHeader("Accept", "application/json");
  
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String response = http.getString();
    
    StaticJsonDocument<256> doc;
    DeserializationError error = deserializeJson(doc, response);
    
    if (!error) {
      currentMode = doc["mode"].as<String>();
      webPumpCommand = doc["pump_command"];
      webGateMainCommand = doc["gate_main_command"];
      webGate1Command = doc["gate1_command"];
      webGate2Command = doc["gate2_command"];
      webGate3Command = doc["gate3_command"];
      webGate4Command = doc["gate4_command"];
      
      Serial.println("Perintah dari server:");
      Serial.println("Mode: " + currentMode);
      Serial.printf("Pump: %d, GateMain: %d\n", webPumpCommand, webGateMainCommand);
    }
  }
  
  http.end();
}

// ================= FUNGSI BACA TANAH =================
int bacaTanahADC(int pin) {
  return analogRead(pin);
}

int kalibrasiTanah(int adc, int dry, int wet) {
  int persen = map(adc, wet, dry, 100, 0);
  return constrain(persen, 0, 100);
}

// ================= ULTRASONIK =================
float bacaAir(int trig, int echo) {
  digitalWrite(trig, LOW); delayMicroseconds(2);
  digitalWrite(trig, HIGH); delayMicroseconds(10);
  digitalWrite(trig, LOW);

  long durasi = pulseIn(echo, HIGH, 30000);

  if (durasi == 0) return 999;

  float jarak = durasi * 0.017;

  if (jarak > 200) jarak = 200;

  return jarak;
}

// ================= FUNGSI BERSIHKAN BARIS LCD =================
void clearLine(byte row) {
  lcd.setCursor(0, row);
  lcd.print("                    ");
}

// ================= SETUP =================
void setup() {
  Serial.begin(115200);
  Wire.begin(21, 22);

  pinMode(TRIG_MAIN, OUTPUT); pinMode(ECHO_MAIN, INPUT);
  pinMode(TRIG_MID, OUTPUT);  pinMode(ECHO_MID, INPUT);
  pinMode(TRIG_TANK, OUTPUT); pinMode(ECHO_TANK, INPUT);

  pinMode(RELAY_PUMP, OUTPUT);
  digitalWrite(RELAY_PUMP, HIGH); // POMPA OFF

  servoMain.attach(SERVO_MAIN);
  servo1.attach(SERVO_1);
  servo2.attach(SERVO_2);
  servo3.attach(SERVO_3);
  servo4.attach(SERVO_4);

  servoMain.write(0);

  lcd.init(); 
  lcd.backlight();
  lcd.setCursor(0,0);
  lcd.print("SISTEM IRIGASI GESTI");
  lcd.setCursor(0,1);
  lcd.print("IoT Monitoring");
  delay(2000);
  
  // Koneksi WiFi
  connectWiFi();
  
  lcd.clear();
}

// ================= LOOP =================
void loop() {

  // ======== BACA SENSOR TANAH ========
  m1 = kalibrasiTanah(bacaTanahADC(SOIL1), DRY1, WET1);
  m2 = kalibrasiTanah(bacaTanahADC(SOIL2), DRY2, WET2);
  m3 = kalibrasiTanah(bacaTanahADC(SOIL3), DRY3, WET3);
  m4 = kalibrasiTanah(bacaTanahADC(SOIL4), DRY4, WET4);

  bool tanahKering = (m1 <= 30 || m2 <= 30 || m3 <= 30 || m4 <= 30);

  // ======== BACA SENSOR ULTRASONIK ========
  airMain = bacaAir(TRIG_MAIN, ECHO_MAIN);
  airMid  = bacaAir(TRIG_MID, ECHO_MID);
  airTank = bacaAir(TRIG_TANK, ECHO_TANK);

  bool tandonAman = (airTank > 0 && airTank <= 12);

  // ======== KATEGORI KETINGGIAN AIR ========
  auto statusAir = [&](float jarak){
    if (jarak >= 0 && jarak <= 4)  return 3;
    if (jarak > 4 && jarak <= 7)   return 2;
    if (jarak > 7 && jarak <= 10)  return 1;
    return 0;
  };

  int levelMid  = statusAir(airMid);
  int levelMain = statusAir(airMain);
  int levelTank = statusAir(airTank);

  bool airMidAda  = (levelMid >= 2);
  bool airMainAda = (levelMain >= 2);
  bool airTankAda = (levelTank >= 2);

  // ======== AMBIL PERINTAH DARI SERVER ========
  if (millis() - lastSendTime >= SEND_INTERVAL) {
    getCommandsFromServer();
  }

  // ======== LOGIKA KONTROL ========
  if (currentMode == "manual") {
    // MODE MANUAL - Kontrol dari website
    pumpStatus = webPumpCommand;
    gateMain = webGateMainCommand;
    gate1 = webGate1Command;
    gate2 = webGate2Command;
    gate3 = webGate3Command;
    gate4 = webGate4Command;
    
    // Jalankan perintah
    digitalWrite(RELAY_PUMP, pumpStatus ? LOW : HIGH);
    servoMain.write(gateMain);
    servo1.write(gate1);
    servo2.write(gate2);
    servo3.write(gate3);
    servo4.write(gate4);
    
    systemStatus = "Manual";
    
  } else {
    // MODE AUTO - Logika fuzzy asli
    
    if (tanahKering) {
      if (airMidAda) {
        servoMain.write(0);
        digitalWrite(RELAY_PUMP, HIGH);
        systemStatus = "Tengah";
        pumpStatus = false;
        gateMain = 0;
      }
      else if (!airMidAda && airMainAda) {
        servoMain.write(90);
        digitalWrite(RELAY_PUMP, HIGH);
        systemStatus = "Utama";
        pumpStatus = false;
        gateMain = 90;
      }
      else if (!airMidAda && !airMainAda && airTankAda && tandonAman) {
        digitalWrite(RELAY_PUMP, LOW);
        servoMain.write(0);
        systemStatus = "Pompa";
        pumpStatus = true;
        gateMain = 0;
      }
      else if (!airMidAda && !airMainAda && !tandonAman) {
        digitalWrite(RELAY_PUMP, HIGH);
        servoMain.write(0);
        systemStatus = "NoAir";
        pumpStatus = false;
        gateMain = 0;
      }
    }
    else {
      servoMain.write(0);
      digitalWrite(RELAY_PUMP, HIGH);
      systemStatus = "Stabil";
      pumpStatus = false;
      gateMain = 0;
    }

    // Kontrol servo per petak
    auto kontrolServoPetak = [&](Servo &s, int kelembapan, int &gateVar){
      if (airMidAda) {
        if (kelembapan <= 30) { s.write(90); gateVar = 90; }
        else if (kelembapan <= 60) { s.write(45); gateVar = 45; }
        else { s.write(0); gateVar = 0; }
      } else {
        s.write(0);
        gateVar = 0;
      }
    };

    kontrolServoPetak(servo1, m1, gate1);
    kontrolServoPetak(servo2, m2, gate2);
    kontrolServoPetak(servo3, m3, gate3);
    kontrolServoPetak(servo4, m4, gate4);
  }

  delay(300);

  // ======== LCD DISPLAY ========
  clearLine(0);
  lcd.setCursor(0,0);
  lcd.printf("P1:%d%% P2:%d%%", m1, m2);

  clearLine(1);
  lcd.setCursor(0,1);
  lcd.printf("P3:%d%% P4:%d%%", m3, m4);

  clearLine(2);
  lcd.setCursor(0,2);
  lcd.print("ST:");
  lcd.print(airMid >= 900 ? "-" : String(airMid,0));
  lcd.print("cm SU:");
  lcd.print(airMain >= 900 ? "-" : String(airMain,0));
  lcd.print("cm");

  clearLine(3);
  lcd.setCursor(0,3);
  lcd.print("SP:");
  lcd.print(airTank >= 900 ? "-" : String(airTank,0));
  lcd.print("cm ");
  lcd.print(systemStatus);
  lcd.print(" ");
  lcd.print(currentMode == "manual" ? "M" : "A");
  lcd.print(" ");
  lcd.print(WiFi.status() == WL_CONNECTED ? "W" : "X");

  // ======== KIRIM DATA KE SERVER ========
  if (millis() - lastSendTime >= SEND_INTERVAL) {
    sendDataToServer();
    lastSendTime = millis();
  }

  // ======== SERIAL MONITOR ========
  Serial.println("===== STATUS SISTEM =====");
  Serial.printf("Mode: %s | WiFi: %s\n", currentMode.c_str(), WiFi.status() == WL_CONNECTED ? "OK" : "FAIL");
  Serial.printf("P1=%d%%  P2=%d%%  P3=%d%%  P4=%d%%\n", m1, m2, m3, m4);
  Serial.printf("Air Tengah=%.1f  Utama=%.1f  Tandon=%.1f\n", airMid, airMain, airTank);
  Serial.printf("Status: %s | Pompa: %s\n\n", systemStatus.c_str(), pumpStatus ? "ON" : "OFF");

  delay(1700); // Total delay ~2 detik
}
