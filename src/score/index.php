<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boca Judge</title>
    <style>

    * {
    margin: 0;
    padding: 0;
}
body {
    font: 14px/1.4 Georgia, Serif;
}
#page-wrap {
    margin: 50px;
}
p {
    margin: 20px 0;
}

    table {
        width: 100%;
        border-collapse: collapse;
    }
    tr:nth-of-type(odd) {
        background: #eee;
    }
    th {
        background: #333;
        color: white;
        font-weight: bold;
    }
    td, th {
        padding: 6px;
        border: 1px solid #ccc;
        text-align: left;
    }
    th {
        text-align: center;
    }
    td:first-child{
        text-align: center;
    }
    td:nth-child(4){
        text-align: center;
    }

    @media
    only screen and (max-width: 760px),
    (min-device-width: 768px) and (max-device-width: 1024px)  {

        table, thead, tbody, th, td, tr {
            display: block;
        }
        thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }

        tr { border: 1px solid #ccc; }

        td {
            border: none;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 50%;
        }

        td:before {
            position: absolute;
            top: 6px;
            left: 6px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
        }
    }

    @media only screen
    and (min-device-width : 320px)
    and (max-device-width : 480px) {
        body {
            padding: 0;
            margin: 0;
            width: 320px; }
        }

    @media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
        body {
            width: 495px;
        }
    }
    </style>
</head>
<body>
    <table id="table-score">
        <thead></thead>
        <tbody></tbody>
    </table>
</body>

<script>
    let np=1
      , html=''
      , answer
      , api_get_problems=()=>{
            return new Promise((rs,rj)=>{
                fetch('/score/api.php?problems=true',{method:'get'})
                .then(r=>r.json())
                .then((data)=>{
                    np=data;
                    html=`<tr><th><b>Posicion</b></th>
                <th><b>Competidor</b></th>
                <th><b>Nombre</b></th>
                <th><b>Grupo</b></th>
                `
                    document.querySelector('#table-score thead').innerHTML=
                    html+data.map((v)=>{
                        return "<th nowrap=''><b>"+v.problem+" &nbsp;</b></th>"
                    }).join('')+"<th><b>Total</b></th></tr>"
                    rs()
                }).catch((err)=>{
                    console.log("err", err);
                    rj()
                })
            });
        }
      , api_request=()=>{
            return new Promise((rs,rj)=>{
                fetch('/score/api.php',{method:'get'})
                .then(r=>r.json())
                .then((data)=>{
                    document.querySelector('#table-score tbody').innerHTML=
                data.map((v,i)=>{
                return `<tr class="sitegroup1">
            <td>${1+i}</td>
            <td nowrap="">${v.username}/${v.site} </td>
            <td>${v.userfullname}</td>
            <td>${v.usericpcid}</td>
            `
                    +np.map((p)=>{
                        answer=v.problem[p.number]||false
                        if(answer&&answer.solved){
                            answer.balloon=p.balloon
                            return `<td nowrap="">
                                <img alt="${answer.colorname}:" width="18" src="${answer.balloon}">
                                <font size="-2">${answer.count}/${answer.time}</font>
                            </td>`
                        }
                        else if(answer&&answer.count){
                            return `<td nowrap="">
                                <font size="-2">${answer.count}/-</font>
                            </td>`
                        }
                        else
                            return '<td nowrap="">&nbsp;&nbsp;</td>'
                    }).join('')
                    +'<td nowrap="">'+v.totalcount+' ('+ v.totaltime+')</td></tr>'
                }).join('')
                    rs()
                }).catch((err)=>{
                    rj()
                })
            });
        }
    api_get_problems().then(api_request)
    setInterval(api_request,7000)
</script>
</html>