import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { Http } from '@angular/http';

@IonicPage()
@Component({
  selector: 'page-test',
  templateUrl: 'test.html',
})
export class TestPage {
  public product: any = {};

  constructor(
  	public navCtrl: NavController,
  	public navParams: NavParams,
    public http: Http
  	) {
	  let url = this.navParams.get('api_url');
	  let product_id = this.navParams.get('product_id');

    this.http.get(url + '/products/' + product_id)
        .map(res => res.json())
        .subscribe(data => {
          console.log(data);
          this.product = data;
        });

  }

  ionViewDidLoad() {
    console.log('ionViewDidLoad TestPage');
  }

}
