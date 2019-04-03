import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, ViewController } from 'ionic-angular';
import { RequestApiProvider, Online } from '../../providers/providers'

/*export interface UserData{[prop: string]: any;}*/
@IonicPage()
@Component({
  selector: 'page-product-category-filter',
  templateUrl: 'product-category-filter.html',
})
export class ProductCategoryFilterPage 
{
  _dataChoose: any = undefined;
  _dataParam: any;
  _typeField: any;
  _dataItems: any;
  title: string = '';
  
  constructor(
    private api: RequestApiProvider,
    private online: Online,
    public navCtrl: NavController,
    public viewCtrl: ViewController,  
    public navParams: NavParams) 
  {
    this.loadData();
  }

  loadData()
  {
    let dataParam = this.navParams.get('modalParam');
    this._dataParam = dataParam;
    //console.log(dataParam)
    this.title = dataParam.name;
    this.getFilter(dataParam.section); 
  }

  async getFilter(endpoint)
  {
    await this.api.get('app/' + endpoint + 'Product', endpoint + 'ProductApp')
    .subscribe((res: any) => {
      let theRes = res[endpoint + 'ProductApp'];
      let theItems = theRes.items;
      this._typeField = theItems.typefield;
      this._dataItems = theItems.field;
    },
		(err) => {
      this.cancel();
      this.online.checkOnline(false);
    })
  }

  choose(val?:any)
  {
    this._dataChoose = val;
  }

  cancel() 
  {
    this.viewCtrl.dismiss();
  }

  done() 
  {
    if(this._dataChoose != undefined)
    {
      let _returnData = {};
      if(this._typeField == 'choose')
      {
        _returnData = {filter:this._dataChoose['name'], categoryname:this._dataChoose['label'], mergefield:false};
      }
      else
      {
        const param = this._dataParam;
        _returnData = {...param, sorting:this._dataChoose['value'], mergefield:true};
      }
      this.viewCtrl.dismiss(_returnData);
    }
  }
}