'use strict'
var ctx = $("#stat");
var chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels:['Mon','Tue','Wen',"Thu","Fri","Sat","Sun"],
      datasets: [
        {
          data: [2,4,8,16,32,64,128],
          label: "Bruted",
          borderColor: "#3e95cd"
        },
        {
          data: [3,10,8,20,14,15,19],
          label: "Uploaded",
          borderColor: "#8e5ea2"
        }
      ]
    },
    options: {
      title: {
        display: true,
        text: "Statistics for 7 days"
      },
      scales: {
        xAxes: [{
          time: {
            unit: 'second'
          }
        }]
      }
    }
});

setInterval(function(){
  $.getJSON("?get_stat", function(result){
    for (var i =0;i< result.length;i++){
      chart.data.datasets[i].data = result[i]
    }
    chart.update()
  })
},10000)
