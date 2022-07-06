const fs = require('fs');

function getArgs() {
    const args = {};
    process.argv.slice(2, process.argv.length).forEach((arg) => {
        // long arg
        if (arg.slice(0, 2) === '--') {
            const longArg = arg.split('=');
            const longArgFlag = longArg[0].slice(2, longArg[0].length);
            const longArgValue = longArg.length > 1 ? longArg[1] : true;
            args[longArgFlag] = longArgValue;
        }
        // flags
        else if (arg[0] === '-') {
            const flags = arg.slice(1, arg.length).split('');
            flags.forEach((flag) => {
                args[flag] = true;
            });
        }
    });
    return args;
}

function formatDate(date) {
    var hours = date.getHours() - 5;
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return date.getMonth() + 1 + '/' + date.getDate() + '/' + date.getFullYear() + '  ' + strTime;
}

function convertUTCDateToLocalDate(date) {
    var newDate = new Date(date.getTime() - date.getTimezoneOffset() * 60 * 1000);
    return newDate;
}

// get args
const args = getArgs();

console.log(args);

// make branch and commit dir regardless of if it exists
fs.mkdirSync(`./public/lighthouse/${args.branch}/${args.commit}`, { recursive: true });

// process branches.json
fs.access('./public/lighthouse/branches.json', fs.F_OK, (err) => {
    if (err) {
        fs.writeFile(
            './public/lighthouse/branches.json',
            JSON.stringify({
                branches: [{ name: args.branch }],
            }),
            (err) => {
                if (err) console.log(err);
                else {
                    console.log('branches.json written successfully\n');
                }
            }
        );
        return;
    }

    fs.readFile('./public/lighthouse/branches.json', (err, data) => {
        if (err) throw err;
        let branches = JSON.parse(data);
        let noBranch = true;

        branches.branches.forEach((branch) => {
            if (branch.name === args.branch) {
                noBranch = false;
            }
        });

        if (noBranch) {
            fs.writeFile(
                './public/lighthouse/branches.json',
                JSON.stringify({
                    branches: [...branches.branches, { name: args.branch }],
                }),
                (err) => {
                    if (err) console.log(err);
                    else {
                        console.log('branches.json written successfully\n');
                    }
                }
            );
        }
    });
});

// process commits.json
fs.access(`./public/lighthouse/${args.branch}/commits.json`, fs.F_OK, (err) => {
    let now = convertUTCDateToLocalDate(new Date());

    if (err) {
        fs.writeFile(
            `./public/lighthouse/${args.branch}/commits.json`,
            JSON.stringify({
                commits: [{ hash: args.commit, date: formatDate(now), dateRaw: now }],
            }),
            (err) => {
                if (err) console.log(err);
                else {
                    console.log('commits.json written successfully\n');
                }
            }
        );
        return;
    }

    fs.readFile(`./public/lighthouse/${args.branch}/commits.json`, (err, data) => {
        if (err) throw err;
        let commits = JSON.parse(data);
        let noCommit = true;
        commits.commits.forEach((commit) => {
            if (commit.hash === args.commit) {
                noCommit = false;
            }
        });

        if (noCommit) {
            if (commits.commits.length === 10) {
                let deletedCommit = commits.commits.pop();

                fs.rmSync(`./public/lighthouse/${args.branch}/${deletedCommit.hash}`, {
                    recursive: true,
                    force: true,
                });
            }

            fs.writeFile(
                `./public/lighthouse/${args.branch}/commits.json`,
                JSON.stringify({
                    commits: [
                        { hash: args.commit, date: formatDate(now), dateRaw: now },
                        ...commits.commits,
                    ],
                }),
                (err) => {
                    if (err) console.log(err);
                    else {
                        console.log('commits.json written successfully\n');
                    }
                }
            );
        }
    });
});
