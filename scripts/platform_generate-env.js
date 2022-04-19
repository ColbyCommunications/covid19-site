const fs = require('fs');

const { env } = process;

// Utility to assist in decoding a packed JSON variable.
function read_base64_json(varName) {
    try {
        return Buffer.from(env[varName], 'base64').toString();
    } catch (err) {
        throw new Error(`no ${varName} environment variable`);
    }
}

// An encoded JSON object.
const env_variables = JSON.parse(read_base64_json('PLATFORM_VARIABLES'));
const passphrase = env_variables.passphrase;

delete env_variables.passphrase;

fs.closeSync(fs.openSync('.env', 'w'));
fs.appendFileSync('.env', `PLATFORM_VARS=${JSON.stringify(env_variables).replace(/\s+/g, '')}`);
fs.appendFileSync('.env', `\nPASSPHRASE="${passphrase}"`);
