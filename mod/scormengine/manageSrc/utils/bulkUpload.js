let request = require("request");
let colors = require("colors");
let glob = require("glob");
let path = require('path');
let fs = require("fs")
const { program } = require('commander');

class Logger {
    constructor(filename){
        if(filename)
        {
            this.stream = fs.createWriteStream(filename);
        } else 
        this.stream = process.stdout;
    }
    log(obj)
    {
        this.stream.write(JSON.stringify(obj,null,4) +"\n");
    }
}


program.version('0.0.1');

program
  .option('-f, --files <path>', 'A glob for selecting file', "./**/*.zip")
  .option('-d, --dryrun', 'Just find the files, no upload', false)
  .option('-l, --log <path>', 'file path for log', "./log.json")
  .option('-k, --key <keyValue>', 'The api key used to authorize access to the API',"1234")
  .option('-u, --url <url>', 'The URL of the Moodle API to use',"http://localhost")
  .option('-r, --resume', 'Resume the previous upload',false)


program.parse(process.argv);

let files;
const options = program.opts();

if(!options.resume)
{
    if(fs.existsSync("./.resume"))
    {
        console.log("A resume file exists. Delete .resume to start over, or run with the -r flag to resume the previous upload");
        process.exit();
    }
    files = glob.GlobSync(options.files)
    files = files.found.filter(i=>/\.zip$/i.test(i))
}
if(options.resume)
{
    if(fs.existsSync("./.resume"))
        files = JSON.parse(fs.readFileSync("./.resume"));
    else
    {
        console.log("Nothing to resume");
        process.exit();
    }
}

if(options.dryrun) 
{
    console.log("Found files:")
    console.log(files);
    process.exit();
}
if(options.log != "stdout")
    console = new Logger(options.log);
const CliProgress = require('cli-progress');

const b1 = new CliProgress.SingleBar({
    format: 'CLI Progress |' + colors.cyan('{bar}') + '| {percentage}% || {value}/{total} Files || {state} {subpercent} || Current: {file}',
    barCompleteChar: '\u2588',
    barIncompleteChar: '\u2591',
    hideCursor: true,
    linewrap:false
});

b1.start(files.length, 0, {
    file: "N/A",
    state:"uploading"
});


function sleep(ms)
{
    return new Promise((res)=>setTimeout(res,ms));
}
async function post(formData, filename)
{
    
    
    return new Promise( (res, rej) =>{
        request.post({
            url:options.url + "/mod/scormengine/service/uploadPackage.php",
            headers:{
                "api-key":options.key
            },
            formData
        },(err,response,body)=>{
            
            if(err)
                return rej({filename,err,body});
            if(response.statusCode !== 302)
                return rej({filename,body});
            console.log({filename,status:"success"})
            res();
        })  
    })
}
async function processFiles()
{
    let f = files.shift();
    while(f)
    {
        try{

            b1.increment({file:path.basename(f)})
            
            
            let size = fs.lstatSync(f).size;
            let bytes = 0;
            const formData = {
                package: fs.createReadStream(f).on('data', (chunk) => {
                    bytes += chunk.length
                    let per = (bytes / size).toFixed(2) * 100;
                    b1.increment(0,{file:path.basename(f),state:"uploading","subpercent": per.toFixed() +"%"});
                    if(per > 99)
                    b1.increment(0,{file:path.basename(f),state:"processing","subpercent": ""});
                })
            }
            await post(formData, f);
        }catch(e)
        {
            console.log(e)
        }
        f = files.shift();
        fs.writeFileSync("./.resume",JSON.stringify(files))

    }
    if(fs.existsSync("./.resume"))
        fs.unlinkSync("./.resume");
    process.exit();
}
processFiles();

