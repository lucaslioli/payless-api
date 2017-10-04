import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http';
import { TestPage } from '../test/test';

import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-products',
  templateUrl: 'products.html',
})
export class ProductsPage {

  private url: string = 'http://payless.ecoagile.com.br';
  public products: Array<{}>;

  constructor(
    public navCtrl: NavController,
    public navParams: NavParams,
    public http: Http
  ) {
    this.http.get(this.url + '/products')
        .map(res => res.json())
        .subscribe(data => {
          this.products = data;
        });
  }

  getProductInfo(id) {
    this.navCtrl.push(TestPage,
    {
      'product_id': id,
      'api_url': this.url
    });
  }

}
