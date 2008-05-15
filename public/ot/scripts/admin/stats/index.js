window.addEvent('domready', function() {

    $('upcomingEventsPieChart').toChart({
            type: 'pie',
            width: 300,
            height: 300,
            backgroundColor: '#FFFFFF',
            colorScheme: '#AF472B'
    });
    
    $('pastEventsPieChart').toChart({
            type: 'pie',
            width: 300,
            height: 300,
            backgroundColor: '#FFFFFF',
            colorScheme: '#464548'
    });
});