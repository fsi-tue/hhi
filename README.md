# FSI HHI (Helfilisten Hosting Interface)

## Introduction
Welcome!
This software provides a web interface for managing shift schedules for various events. Similar to the CCC's Engelsystem, it allows a digital shift schedule to be distributed to all potential volunteers.
The only requirements are PHP (>=8) and a HTTP server. Configuration and data storage are handled via two JSON files.

## Configuration
There are two JSON files: `config.json` for the main application configuration and `shifts.json` (can be changed) for the actual shift schedule. Thera are also sample files for these configurations. If you don't want to start from scratch, just copy them for your actual configuration:

```
cp sample-config.json config.json
cp sample-shifts.json shifts.json
```

### Details: `config.json`
| Key               | Description                                                                 | Required  |
|-------------------|-----------------------------------------------------------------------------|-----------|
| `shiftFile`       | Path to JSON with shift definitions (see next chapter)                      | yes       |
| `baseUrl`         | The base URL of the service, usually ending with `index.php`                | yes       |
| `hashSalt`        | Salt for the registration hashs                                             | yes       |
| `enableRegister`  | Enable or disable registration function                                     | yes       |
| `enableUnegister` | Enable or disable unregistration function                                   | yes       |
| `mail.username`   | Username for SMTP server                                                    | yes       |
| `mail.password`   | Password for SMTP server                                                    | yes       |
| `mail.smtpserv`   | Address of SMTP server (notice: we always connect via STARTTLS on port 587) | yes       |
| `mail.fromaddress`| Sender's mail address                                                       | yes       |
| `mail.fromname`   | Sender's human readable name                                                | yes       |

*The key name in this table follows the syntax `key.subkey` => `{"key": {"subkey": value}}`*

### Details: `shifts.json`
The shift definition uses the following hierarchical structure:
- There is just one **Event** (like *Clubhausfest*)
- An **Event** has multiple **Tasks** (like *Main Bar*)
- A **Task** has multiple **Shifts** (like *19.00 PM to 21.00 PM*)
- A **Shifts** provides a limited amount of **Slots** (like *10 persons max*)
- A **Slot** is filled with an **Entry** (like *fsi.sebastian*)

For an example, have a look at `sample-shifts.json`.

## Test this project
Just run `make test` (because Makefiles are superior). This will host a local webserver which listens on `0.0.0.0:8080` for testing purposes.

## Contribution
If you want to contribute: Feel free! 
There is a file called `AUTHORS` in the repository root. If you want your name to be displayed within the website, you can just add a line with your name there.