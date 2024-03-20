import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import { readdirSync,lstatSync } from 'fs';
import { resolve } from 'path';
import inject from "@rollup/plugin-inject";

function getFilesFromDir(dir) {
    const filesToReturn = [];

    function walkDir(currentPath) {

        if(lstatSync(currentPath).isFile()){
            filesToReturn.push(currentPath);
            return;
        }

        const files = readdirSync(currentPath);
        for (let i in files) {
            console.log(files[i]);
            const curFile = resolve(currentPath, files[i]);
            if(lstatSync(curFile).isDirectory()){
                walkDir(curFile);
                continue;
            }
            const file = resolve(currentPath, files[i]);
            filesToReturn.push(file);
        }
    }

    walkDir(resolve(__dirname, dir));
    return filesToReturn;
}


// Get all .css and .scss files in the directory
// const pageStyles = getFilesFromDir('./resources/css');
const js = getFilesFromDir('./resources/js');

const paths = [

    // ...pageStyles,
    ...js,

    'node_modules/jquery/dist/jquery.js',
    'node_modules/jscroll/dist/jquery.jscroll.min.js',

    'node_modules/bootstrap/dist/css/bootstrap.css',
    'node_modules/bootstrap/dist/js/bootstrap.bundle.js',

    'node_modules/@fortawesome/fontawesome-free/css/all.css',

    "node_modules/easyeditor/src/easyeditor.css",
    "node_modules/easyeditor/src/jquery.easyeditor.js"
]

console.log(paths);

export default defineConfig(({command,mode})=>{
    return {
        plugins: [
            laravel({
                input: paths,
                refresh: true,
            }),
            inject({
                $: 'jquery',
                jQuery: 'jquery',
                bootstrap: "bootstrap"
            }),
        ],
        resolve:{
            alias:{
                '@utils': resolve(__dirname, "resources/js/utils"),
                '@jquery-plugins/easyeditor':resolve(__dirname,"node_modules/easyeditor/src/jquery.easyeditor.js"),
            }
        },
        build: {
            minify: mode=='dev'?false:true
        }
    }
});
