import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http';

@IonicPage()
@Component({
  selector: 'page-products',
  templateUrl: 'products.html',
})
export class ProductsPage {

  private url: string = 'http://localhost'

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    public http: Http
  ) {

  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad ProductsPage');
  }

}
