import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.util.Scanner;

/**
 * WhatsGateway — Java Quickstart
 * Compatible with Java 8, 11, 17, and 21+ using standard HttpURLConnection.
 * Zero external JAR dependencies required.
 *
 * Usage:
 *   # Interactive mode:
 *   java SendWhatsApp
 *
 *   # Command line mode:
 *   java SendWhatsApp 919876543210 "Hello from Java!"
 */
public class SendWhatsApp {

    private static final String DEFAULT_URL = "https://whatsapp-api-xepo.onrender.com/api/send.php";

    public static void main(String[] args) {
        String apiKey = System.getenv("WA_API_KEY");
        String apiUrl = System.getenv("WA_API_URL");
        if (apiUrl == null || apiUrl.trim().isEmpty()) {
            apiUrl = DEFAULT_URL;
        }

        Scanner scanner = new Scanner(System.in);

        if (apiKey == null || apiKey.trim().isEmpty()) {
            System.out.print("Enter your API Key (wa_live_...): ");
            if (scanner.hasNextLine()) {
                apiKey = scanner.nextLine().trim();
            }
            if (apiKey == null || apiKey.trim().isEmpty()) {
                System.err.println("[!] API Key is required.");
                System.exit(1);
            }
        }

        String to = "";
        String message = "";

        if (args.length >= 2) {
            to = args[0].trim();
            message = args[1].trim();
        } else {
            System.out.print("Enter recipient phone number (with country code): ");
            if (scanner.hasNextLine()) {
                to = scanner.nextLine().trim();
            }
            System.out.print("Enter message text: ");
            if (scanner.hasNextLine()) {
                message = scanner.nextLine().trim();
            }
        }

        System.out.println("\n[*] Sending message via Standard Format...");
        System.out.println("[*] Endpoint:  " + apiUrl);
        System.out.println("[*] Recipient: " + to);

        try {
            boolean success = sendMessage(apiUrl, apiKey, to, message);
            if (!success) {
                System.exit(1);
            }
        } catch (Exception e) {
            System.err.println("\n[-] Error: " + e.getMessage());
            e.printStackTrace();
            System.exit(1);
        }
    }

    public static boolean sendMessage(String apiUrl, String apiKey, String to, String message) throws Exception {
        URL url = new URL(apiUrl);
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        conn.setRequestMethod("POST");
        conn.setRequestProperty("Content-Type", "application/json; charset=UTF-8");
        conn.setRequestProperty("X-API-Key", apiKey);
        conn.setConnectTimeout(15000);
        conn.setReadTimeout(20000);
        conn.setDoOutput(true);

        // Escape JSON characters
        String escapedMessage = message
                .replace("\\", "\\\\")
                .replace("\"", "\\\"")
                .replace("\n", "\\n")
                .replace("\r", "\\r");

        String jsonPayload = "{\"to\":\"" + to + "\",\"message\":\"" + escapedMessage + "\",\"humanize\":true}";

        byte[] postBytes = jsonPayload.getBytes(StandardCharsets.UTF_8);
        try (OutputStream os = conn.getOutputStream()) {
            os.write(postBytes);
            os.flush();
        }

        int statusCode = conn.getResponseCode();
        InputStream is = (statusCode >= 200 && statusCode < 400) ? conn.getInputStream() : conn.getErrorStream();

        StringBuilder responseBuilder = new StringBuilder();
        if (is != null) {
            try (BufferedReader reader = new BufferedReader(new InputStreamReader(is, StandardCharsets.UTF_8))) {
                String line;
                while ((line = reader.readLine()) != null) {
                    responseBuilder.append(line).append("\n");
                }
            }
        }

        String responseBody = responseBuilder.toString().trim();

        if (statusCode >= 200 && statusCode < 300) {
            System.out.println("\n[+] SUCCESS! HTTP " + statusCode + ":");
            System.out.println(responseBody);
            return true;
        } else {
            System.err.println("\n[-] FAILED! HTTP " + statusCode + ":");
            System.err.println(responseBody);
            return false;
        }
    }
}
