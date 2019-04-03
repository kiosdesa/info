import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ProductCategoryModalPage } from './product-category-modal';

@NgModule({
  declarations: [
    ProductCategoryModalPage,
  ],
  imports: [
    IonicPageModule.forChild(ProductCategoryModalPage),
    TranslateModule.forChild()
  ],
})
export class ProductCategoryModalPageModule {}
