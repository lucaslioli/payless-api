import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { BarcodeScanner, BarcodeScannerOptions } from '@ionic-native/barcode-scanner';

/**
 * Generated class for the ScannerPage page.
 *
 * See http://ionicframework.com/docs/components/#navigation for more info
 * on Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-scanner',
  templateUrl: 'scanner.html',
})
export class ScannerPage {

  options: BarcodeScannerOptions;
  dados;

  constructor(public navCtrl: NavController, public navParams: NavParams, private scanner: BarcodeScanner) {
  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad ScannerPage');
  }

  escanear(){

  	this.options = {
  		prompt: "Escaneie um QR Code!"
  	}

  	this.scanner.scan(this.options).then(barcodeData => {
  		this.dados = barcodeData;
  	})
  	
  }

}
