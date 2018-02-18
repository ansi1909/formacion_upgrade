// 1. Configure Scheduler Basic Settings
scheduler.config.xml_date="%Y-%m-%d %H:%i";
scheduler.config.first_hour = 6;
scheduler.config.last_hour = 24;
scheduler.config.limit_time_select = true;
scheduler.config.details_on_create = true;
// Disable event edition with single click
scheduler.config.select = false;
scheduler.config.details_on_dblclick = true;
scheduler.config.max_month_events = 5;
scheduler.config.resize_month_events = true;

// 2. Configure Lightbox (form) sections
scheduler.config.lightbox.sections = [
    // If you have another field on your Appointment entity (e.g example_field column), you would add it like
    // {name:"Example Field", height:30, map_to:"example_field", type:"textarea"},
    {name:"Nombre", height:30, map_to:"text", type:"textarea"},
    {name:"Descripción", height:30, map_to:"descripcion", type:"textarea"},
    {name:"Lugar", height:30, map_to:"lugar", type:"textarea"},
    {name:"Empresa", options: window.GLOBAL_EMPRESAS, map_to: "empresa", type: "select", height:30 },
    {name:"Nivel", options: window.GLOBAL_NIVELES , map_to: "nivel", type: "select", height:30 },
    {name:"Tiempo", height:72, type:"time", map_to:"auto"}
];

// 3. Start calendar with custom settings
var initSettings = {
    // Element where the scheduler will be started
    elementId: "scheduler_element",
    // Date object where the scheduler should be started
    startDate: new Date(),
    // Start mode
    mode: "month"
};

scheduler.init(initSettings.elementId, initSettings.startDate , initSettings.mode);

// 4. Parse the initial (From index controller) appointments
scheduler.parse(window.GLOBAL_APPOINTMENTS, "json");

// 5. Function that formats the events to the expected format in the server side

/**
 * Returns an Object with the desired structure of the server.
 * 
 * @param {*} id 
 * @param {*} useJavascriptDate 
 */
function getFormatedEvent(id, useJavascriptDate){
    var event;

    // If id is already an event object, use it and don't search for it
    if(typeof(id) == "object"){
        event = id;
    }else{
        event = scheduler.getEvent(parseInt(id));
    }

    if(!event){
        console.error("The ID of the event doesn't exist: " + id);
        return false;
    }
     
    var start , end;
    
    if(useJavascriptDate){
        start = event.start_date;
        end = event.end_date;
    }else{
        start = moment(event.start_date).format('DD-MM-YYYY HH:mm:ss');
        end = moment(event.end_date).format('DD-MM-YYYY HH:mm:ss');
    }
    
    return {
        id: event.id,
        start_date : start,
        end_date : end,
        descripcion : event.descripcion,
        lugar : event.lugar,
        title : event.text,
        empresa: event.empresa,
        nivel: event.nivel
    };
}

// 6. Attach Event Handlers !

/**
 * Handle the CREATE scheduler event
 */
scheduler.attachEvent("onEventAdded", function(id,ev){
    var schedulerState = scheduler.getState();
    
    $.ajax({
        url:  window.GLOBAL_SCHEDULER_ROUTES.create,
        data: getFormatedEvent(ev),
        dataType: "json",
        type: "POST",
        success: function(response){
            // Very important:
            // Update the ID of the scheduler appointment with the ID of the database
            // so we can edit the same appointment now !
            
            scheduler.changeEventId(ev.id , response.id);

            alert('El evento '+ev.text+ " ha sido creado");
        },
        error:function(error){
            alert('Error: El evento '+ev.text+' no fue creado');
            console.log(error);
        }
    }); 
});

/**
 * Handle the UPDATE event of the scheduler on all possible cases (drag and drop, resize etc..)
 *  
 */
scheduler.attachEvent("onEventChanged", function(id,ev){
    $.ajax({
        url:  window.GLOBAL_SCHEDULER_ROUTES.update,
        data: getFormatedEvent(ev),
        dataType: "json",
        type: "POST",
        success: function(response){
            if(response.status == "success"){
                alert("Evento atualizado con éxito!");
            }
        },
        error: function(err){
            alert("Error: No se puedo actualizar el evento");
            console.error(err);
        }
    });

    return true;
});

/**
 * Handle the DELETE appointment event
 */
scheduler.attachEvent("onConfirmedBeforeEventDelete",function(id,ev){
    $.ajax({
        url: window.GLOBAL_SCHEDULER_ROUTES.delete,
        data:{
            id: id
        },
        dataType: "json",
        type: "DELETE",
        success: function(response){
            if(response.status == "success"){
                if(!ev.willDeleted){
                    alert("Evento atualizado con éxito!");
                }
            }else if(response.status == "error"){
                alert("Error: No pudo eliminar el evento");
            }
        },
        error:function(error){
            alert("Error: No puedo eliminar el evento: " + ev.text);
            console.log(error);
        }
    });
    
    return true;
});


/**
 * Edit event with the right click too
 * 
 * @param {type} id
 * @param {type} ev
 * @returns {Boolean}
 */
scheduler.attachEvent("onContextMenu", function (id, e){
    scheduler.showLightbox(id);
    e.preventDefault();
});