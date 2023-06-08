define([
  'uiComponent',
  'jquery'
], (Component, $,) => {
  'use strict';

  return Component.extend({
    defaults: {
      apiKey: '',
      coordinatesData: []
    },

    initialize() {
      this._super();

      console.log(this.apiKey);
      console.log('Datos de coordenadas:', this.coordinatesData);

      this.getCurrentPosition();
    },

    getCurrentPosition() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          position => this.handlePositionSuccess(position),
          error => this.handlePositionError(error)
        );
      } else {
        console.log('Geolocalización no soportada por el navegador.');
      }
    },

    handlePositionSuccess(position) {
      const { latitude, longitude } = position.coords;
      console.log('Latitud: ' + latitude);
      console.log('Longitud: ' + longitude);

      this.calculateDistances(latitude, longitude)
        .then(distances => {
          this.getAddressFromCoordinates(latitude, longitude)
            .then(response => {
              if (response.status === 'OK' && response.results.length > 0) {
                const address = response.results[0].formatted_address;
                console.log('Dirección:', address);

                const userData = {
                  address: address,
                  distance: distances
                };

                this.saveDataToCustomerData(userData);

                // const addressParts = address.split(",");
                // const street = [addressParts[0].trim()];
                // const city = [addressParts[1].trim()];
                // const region = [addressParts[2].trim()];
                // const country = [addressParts[3].trim()];

                
                // // $(document).ready(function() {
                //   //   $('[name="street[0]"]').val(street);
                //   //   $('[name="city"]').val(city);
                //   //   $('[name="city"]').val(city);
                //   //   const selectElement = $('[name="region_id]');
                //   //   selectElement.find(`option[data-title="${region}"]`).prop('selected', true);
                //   // });
                  
                //   setTimeout(function () {
                //     $('[name="street[0]"]').val(street);
                //     $('[name="city"]').val(city);
                //     const selectReqion = $('[name="region_id"]');
                //     selectReqion.find(`option[data-title="${region}"]`).prop('selected', true);
                //     const selectCountry = $('[name="region_id"]');
                //     selectCountry.find(`option[data-title="${country}"]`).prop('selected', true);
                // }, 2000);

              } else {
                console.log('No se pudo obtener la dirección.');
              }
            })
            .catch(() => console.log('Error al obtener la dirección.'));
        })
        .catch(() => console.log('Error al calcular las distancias.'));
    },

    handlePositionError(error) {
      console.log('Error al obtener la ubicación:', error);
    },

    calculateDistances(userLatitude, userLongitude) {
      return new Promise((resolve, reject) => {
        const earthRadius = 6371; // Radio de la Tierra en kilómetros
        const distances = [];

        this.coordinatesData.forEach(coordinate => {
          const productLatitude = coordinate.latitude;
          const productLongitude = coordinate.longitude;

          const dLat = this.toRadians(productLatitude - userLatitude);
          const dLon = this.toRadians(productLongitude - userLongitude);

          const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(this.toRadians(userLatitude)) * Math.cos(this.toRadians(productLatitude)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);

          const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
          const distance = earthRadius * c;

          console.log('Distancia al producto:', distance, 'km');
          distances.push(distance);
        });

        resolve(distances);
      });
    },

    toRadians(degrees) {
      return degrees * Math.PI / 180;
    },

    getAddressFromCoordinates(latitude, longitude) {
      return new Promise((resolve, reject) => {
        $.ajax({
          url: 'https://maps.googleapis.com/maps/api/geocode/json',
          data: {
            latlng: `${latitude},${longitude}`,
            key: this.apiKey
          },
          success: response => resolve(response),
          error: () => reject()
        });
      });
    },

    saveDataToCustomerData(userData) {
      console.log(userData);
      $.ajax({
        url: '/daviddelgado_googlecoordinates/proceso/procesar',
        method: 'POST',
        data: { userData: userData },
        success: function(response) {
          console.log('Datos enviados correctamente');
        },
        error: function(xhr, status, error) {
          console.log('Error al enviar los datos');
          console.log(error);
        }
      });
    },
  });
});
